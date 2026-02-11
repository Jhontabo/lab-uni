<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email? : Email de destino para la prueba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el envío de correos electrónicos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?: $this->ask('Ingrese el email de destino para la prueba');

        if (! $email) {
            $this->error('Debe proporcionar un email de destino');

            return 1;
        }

        $this->info('Enviando correo de prueba a: '.$email);

        try {
            // Enviar correo de prueba
            Mail::raw('Este es un correo de prueba del sistema de gestión de laboratorios. Si recibes este mensaje, la configuración de correo es correcta.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Correo de Prueba - Sistema de Laboratorios');
            });

            // Enviar correo de prueba
            Mail::raw('Este es un correo de prueba del sistema de gestión de laboratorios. Si recibes este mensaje, la configuración de correo es correcta.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Correo de Prueba - Sistema de Laboratorios');
            });

            $this->info('✅ Correo de prueba enviado exitosamente a: '.$email);

            $this->info('Si no recibes el correo, verifica:');
            $this->info('1. Las credenciales de Gmail en el archivo .env');
            $this->info('2. Que tengas una contraseña de aplicación configurada');
            $this->info('3. La carpeta de spam del correo');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error al enviar el correo: '.$e->getMessage());
            $this->error('Verifica la configuración de MAIL_* en tu archivo .env');

            return 1;
        }
    }
}
