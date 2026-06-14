<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDOException;

class CreateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:create:pgsql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear la base de datos PostgreSQL si no existe';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $pdo = new \PDO('pgsql:host=' . env('DB_HOST') . ';port=' . env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));

            $databaseName = env('DB_DATABASE');
            $query = "SELECT 1 FROM pg_database WHERE datname = '$databaseName'";
            $result = $pdo->query($query);

            if ($result && $result->fetch()) {
                $this->info("La base de datos '$databaseName' ya existe.");

                $pdo->exec("DROP DATABASE $databaseName");
                $this->info("Base de datos '$databaseName' eliminada con éxito.");
            } else {
                $pdo->exec("CREATE DATABASE $databaseName");
                $this->info("Base de datos '$databaseName' creada con éxito.");

                $pdo->exec("GRANT ALL ON SCHEMA public TO $databaseName;");
                $pdo->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO $databaseName;");
                $pdo->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO $databaseName;");

                $this->info("Permisos sobre Base de datos '$databaseName' otorgados a '$databaseName' con éxito.");
            }
        } catch (PDOException $e) {
            $this->error("Error al conectar o crear la base de datos: " . $e->getMessage());
        }
    }
}
