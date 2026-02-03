<?php


class Logger
{
    private string $context;

    public function __construct(string $context) {
        $this->context = $context;
    }

    public function log(string $message, string $level = 'INFO'): void
    {
        $date = date('Y-m-d H:i:s');

        $formatted = sprintf(
            "[%s] [%s] %s\n",
            $date,
            strtoupper($level), "[$this->context] " . $message
        );
        // Write to server terminal (stderr)
        file_put_contents('php://stderr', $formatted);
    }

    public  function info(string $message): void
    {
        $this->log($message, 'INFO');
    }

    public  function warning(string $message): void
    {
        $this->log($message, 'WARNING');
    }

    public  function error(string $message): void
    {
        $this->log($message, 'ERROR');
    }
}
