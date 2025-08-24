<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class TransferUsersData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transfer-users-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer users data from users table to dts_users table with encryption';

    
    protected $encryptionKey = 'd3p3d10@ict';
    protected $emailEncryptionKey = 'd3p3d10@ict';

   

     
     protected function encryptSensitiveField($key, $value)
     {
         if (in_array($key, ['name', 'position'])) {
             return Crypt::encryptString($value);
         }
         return $value; 
     }

    protected function encryptData($plaintext, $key) {
        $method = 'aes-256-gcm';
        $key = hash('sha256', $key, true);
        $iv = substr(hash('sha256', 'fixed_iv_value'), 0, 12);
        $tag = '';

        $encrypted = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);

        return bin2hex($tag . $encrypted);
    }


    public function handle()
    {
        $this->info('Starting users data transfer...');
        
        try {
           
            $users = DB::table('users')->get();
            
            $this->info('Found ' . count($users) . ' users to transfer.');
            
            $bar = $this->output->createProgressBar(count($users));
            $bar->start();

            $successCount = 0;
            $errorCount = 0;
            
            foreach ($users as $user) {
                try {
                    
                    $encryptedName = $this->encryptSensitiveField('name', $user->fullname);
                    $encryptedPosition = $this->encryptSensitiveField('position', $user->designation);

                    $encryptedEmail = $this->encryptData($user->email, $this->emailEncryptionKey);
                    
                    
                    $hashedPassword = Hash::make($user->password);
                    
                    
                    $now = Carbon::now();
                    
                    
                    DB::table('dts_users')->insert([
                        'id' => $user->users_id,
                        'name' => $encryptedName,
                        'email' => $encryptedEmail,
                        'email_verified_at' => $now,
                        'password' => $hashedPassword,
                        'position' => $encryptedPosition,
                        'section_id' => $user->dts_section_id,
                        'user_type' => $user->user_type,
                        'station_id' => $user->station_id,
                        'system_admin' => $user->system_admin,
                        'approved' => $user->approved,
                        'active' => $user->active,
                        'contactnumber' => $user->contact_number,
                        'created_at' => $now,
                        'updated_at' => $now,
                        // Adding remaining fields with default values
                        'two_factor_secret' => null,
                        'two_factor_recovery_codes' => null,
                        'two_factor_confirmed_at' => null,
                        'remember_token' => null,
                        'division_code' => null,
                        'office_nameid' => null,
                        'google_id' => null,
                        'google_token' => null,
                        'google_refresh_token' => null,
                        'avatar' => $user->dts_image_url ?? null,
                        'role' => null,
                        'security_code' => null
                    ]);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("Error processing user ID {$user->users_id}: " . $e->getMessage());
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            $this->info("Data transfer completed: $successCount users transferred successfully, $errorCount failures.");
            
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . " on line " . $e->getLine());
            return 1; // Command failed
        }
        
        return 0; // Command succeeded
    }
}