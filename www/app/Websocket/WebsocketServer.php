<?php

declare(strict_types=1);

namespace App\Websocket;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class WebsocketServer
{
    private array $config;
    private mixed $socket;
    private array $connects = [];

    private AMQPStreamConnection $rabbitConn;

    /**
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->rabbitConn = new AMQPStreamConnection(
            $this->config['rabbit']['host'],
            $this->config['rabbit']['port'],
            $this->config['rabbit']['user'],
            $this->config['rabbit']['password']
        );
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $address = $this->config['transport'] . '://' . $this->config['host'] . ':' . $this->config['port'];

        $ssl_context = stream_context_create(['ssl' => $this->config['ssl']]);
        $this->socket = stream_socket_server(
            $address,
            $errorNo,
            $errorStr,
            STREAM_SERVER_BIND|STREAM_SERVER_LISTEN,
            $ssl_context
        );

        if ($this->socket === false) {
            throw new Exception(sprintf('Socket server create error #%s: %s', $errorNo, $errorStr));
        }

        echo 'socket created: ' . $address . PHP_EOL;


        while (true) {
            //формируем массив прослушиваемых сокетов:
            $read = $this->connects;
            $read[] = $this->socket;
            $write = $except = null;

            if (!stream_select($read, $write, $except, null)) {//ожидаем сокеты доступные для чтения (без таймаута)
                echo 'stream_select'.PHP_EOL;
                break;
            }

            if (in_array($this->socket, $read)) {//есть новое соединение
                $connect = stream_socket_accept($this->socket, -1);//принимаем новое соединение
                $this->connects[] = $connect;//добавляем его в список необходимых для обработки
                unset($read[ array_search($this->socket, $read) ]);

                echo 'new connection from: '.stream_socket_get_name($connect, true).stream_socket_get_name($connect, false).PHP_EOL;

                stream_set_blocking($connect, true);
                $headers = fread($connect, 1500);
                $this->handshake($connect, $headers);
                stream_set_blocking($connect, false);

                echo var_export(array_keys($this->connects), true).PHP_EOL;
            }

            foreach($read as $connect) {//обрабатываем все соединения
                $content = stream_get_contents($connect);

                try {
                    $payload = $this->decode($content);
                    echo "payload from `".stream_socket_get_name($connect, true)."`:\n".var_export($payload, true).PHP_EOL;

                    if ($payload === null) {
                        continue;
                    }

                    switch ($payload['type']) {
                        case 'close':
                            unset($this->connects[ array_search($connect, $this->connects) ]);
                            break;
                        case 'text':
                            $msg = json_decode($payload['payload'], true);

                            //TODO Список id друзей получать из списка авторизованного пользователя

                            $ids = explode(',', $msg['ids']);

                            if (!$this->send('Listen messages from friends by ids: ' . $msg['ids'], $connect)) {
                                throw new Exception('error file write to client');
                            }

                            $channel = $this->rabbitConn->channel();
                            $channel->exchange_declare('amq.topic', 'topic', false, true, false);
                            foreach ($ids as $id) {
                                $channel->queue_bind('news', 'amq.topic', 'user:' . $id);
                            }

                            $channel->basic_consume(
                                'news',
                                '',
                                false,
                                true,
                                false,
                                false,
                                function (AMQPMessage $msg) use ($connect) {
                                    $data = json_decode($msg->getBody(), true);
                                    $message = 'Новый пост от пользователя #' . $data['user_id'] . ': ' . $data['message'];
                                    $this->send($message, $connect);
                                }
                            );

                            while(count($channel->callbacks)) {
                                $channel->wait();
                            }

                            break;
                        default:
                            break;
                    }
                } catch (Exception $exception) {
                    echo 'Payload error: ' . $exception->getMessage() . PHP_EOL;
                    unset($this->connects[ array_search($connect, $this->connects) ]);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function send(string $message, $connect): bool
    {
        return (bool) fwrite($connect, $this->encode($message));
    }

    public function __destruct()
    {
        $this->socket !== false && stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
    }

    protected function handshake($client, $received): void
    {
        $headers = [];
        $lines = preg_split("/\r\n/", $received);
        foreach($lines as $line)
        {
            $line = rtrim($line);
            if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)){
                $headers[$matches[1]] = $matches[2];
            }
        }

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        //hand shaking header
        $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: " . $this->config['host'] . "\r\n" .
            "WebSocket-Location: wss://" . $this->config['host'] . ":" . $this->config['port'] . "\r\n".
            "Sec-WebSocket-Version: 13\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        fwrite($client, $upgrade);
    }

    /**
     * @throws Exception
     */
    protected function decode($data): ?array
    {
        $unmaskedPayload = '';
        $decodedData = [];

        if (!isset($data[0]) || !isset($data[1])) {
            throw new Exception('data error (1001)');
        }

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));

        $isMasked = $secondByteBinary[0] == '1';
        // unmasked frame is received:
        if (!$isMasked) {
            throw new Exception('protocol error (1002)');
        }

        $payloadLength = ord($data[1]) & 127;

        $decodedData['type'] = match ($opcode) {
            1 => 'text',
            2 => 'binary',
            8 => 'close',
            9 => 'ping',
            10 => 'pong',
            default => throw new Exception('unknown opcode (1003)'),
        };

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferred.
         */
        if (strlen($data) < $dataLength) {
            return null;
        }

        for ($i = $payloadOffset; $i < $dataLength; $i++) {
            $j = $i - $payloadOffset;
            if (isset($data[$i])) {
                $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
            }
        }
        $decodedData['payload'] = $unmaskedPayload;

        return $decodedData;
    }

    /**
     * @throws Exception
     */
    protected function encode($payload, $type = 'text', $masked = false): string
    {
        $frameHead = [];
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                //return $payload;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                throw new Exception('Payload frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = [];
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }
}
