<?php
declare(strict_types=1);
namespace App\Counter;

// アプリケーション設定
$config = [
    'database' => [
        'host' => 'localhost',
        'user' => 'localuser',
        'pass' => 'password',
        'database' => 'mydb'
    ],
    'app' => [
        'debug' => true,  // デバッグモードの設定
        'charset' => 'utf8mb4'
    ]
];

// エラー設定
ini_set('display_errors', 'On');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class CounterApp
{
    private ?\mysqli $connection = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function run(): void
    {
        try {
            $this->connect();
            $count = $this->incrementAndGetCount();
            $this->renderPage($count);
        } catch (\Exception $e) {
            $this->handleError($e);
        } finally {
            $this->closeConnection();
        }
    }

    private function connect(): void
    {
        $dbConfig = $this->config['database'];
        $this->connection = new \mysqli(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['pass'],
            $dbConfig['database']
        );

        if ($this->connection->connect_error) {
            throw new \RuntimeException('Database connection failed: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset($this->config['app']['charset']);
    }

    private function incrementAndGetCount(): int
    {
        $this->connection->begin_transaction();

        try {
            $this->connection->query("UPDATE counter SET count = count + 1");
            $result = $this->connection->query("SELECT count FROM counter");
            $count = $result->fetch_row()[0];

            $this->connection->commit();
            return (int)$count;
        } catch (\Exception $e) {
            $this->connection->rollback();
            throw $e;
        }
    }

    private function renderPage(int $count): void
    {
        $template = <<<HTML
        <!DOCTYPE html>
        <html lang="ja">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>アクセスカウンター</title>
            <style>
                body {
                    font-family: 'Helvetica Neue', Arial, sans-serif;
                    margin: 2rem auto;
                    max-width: 800px;
                    padding: 0 1rem;
                    background-color: #f5f5f5;
                }
                .counter-container {
                    background: white;
                    padding: 2rem;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #333;
                    margin-bottom: 1.5rem;
                }
                .count-display {
                    font-size: 1.25rem;
                    color: #2c3e50;
                }
            </style>
        </head>
        <body>
            <div class="counter-container">
                <h1>アクセスカウンター</h1>
                <div class="count-display">
                    現在の訪問数: <strong>%d</strong>
                </div>
            </div>
        </body>
        </html>
        HTML;

        printf($template, $count);
    }

    private function handleError(\Exception $e): void
    {
        http_response_code(500);
        if ($this->config['app']['debug']) {
            echo '<h1>エラーが発生しました</h1>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        } else {
            echo '<h1>何らかのエラーが発生しました</h1>';
        }
    }

    private function closeConnection(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// アプリケーションの実行
$app = new CounterApp($config);
$app->run();
