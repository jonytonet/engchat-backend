<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ErpContactSyncDTO;
use App\DTOs\ErpUserSyncDTO;
use App\Models\Contact;
use App\Models\User;
use App\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErpIntegrationService
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository
    ) {}

    /**
     * Synchronize user with ERP system
     */
    public function syncUser(ErpUserSyncDTO $dto): ?User
    {
        try {
            DB::beginTransaction();

            // Buscar usuário existente por ERP ID
            $user = User::findByErpUserId($dto->erpUserId);

            if ($user) {
                // Atualizar usuário existente
                $user->syncWithErp($dto->toArray());
                Log::info('User synced with ERP', [
                    'user_id' => $user->id,
                    'erp_user_id' => $dto->erpUserId
                ]);
            } else {
                // Criar novo usuário
                $userData = $dto->toArray();
                $userData['password'] = bcrypt('temporary_password'); // Senha temporária
                $userData['email_verified_at'] = now();

                $user = User::create($userData);
                Log::info('New user created from ERP', [
                    'user_id' => $user->id,
                    'erp_user_id' => $dto->erpUserId
                ]);
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync user with ERP', [
                'erp_user_id' => $dto->erpUserId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Synchronize contact with ERP system
     */
    public function syncContact(ErpContactSyncDTO $dto): ?Contact
    {
        try {
            DB::beginTransaction();

            // Buscar contato existente por Business Partner ID
            $contact = Contact::findByBusinessPartnerId($dto->businessPartnerId);

            if ($contact) {
                // Atualizar contato existente
                $contact->syncWithErp($dto->toArray());
                Log::info('Contact synced with ERP', [
                    'contact_id' => $contact->id,
                    'businesspartner_id' => $dto->businessPartnerId
                ]);
            } else {
                // Criar novo contato
                $contactData = $dto->toArray();
                $contactData['preferred_language'] = $contactData['preferred_language'] ?? 'pt-BR';
                $contactData['timezone'] = $contactData['timezone'] ?? 'America/Sao_Paulo';

                $contact = Contact::create($contactData);
                Log::info('New contact created from ERP', [
                    'contact_id' => $contact->id,
                    'businesspartner_id' => $dto->businessPartnerId
                ]);
            }

            DB::commit();
            return $contact;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync contact with ERP', [
                'businesspartner_id' => $dto->businessPartnerId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Batch sync users from ERP
     */
    public function batchSyncUsers(array $users): array
    {
        $results = [
            'success' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($users as $userData) {
            try {
                $dto = ErpUserSyncDTO::fromArray($userData);
                $user = $this->syncUser($dto);

                $results['success']++;
                $results['details'][] = [
                    'erp_user_id' => $dto->erpUserId,
                    'status' => 'success',
                    'user_id' => $user->id
                ];
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'erp_user_id' => $userData['erp_user_id'] ?? 'unknown',
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Batch sync contacts from ERP
     */
    public function batchSyncContacts(array $contacts): array
    {
        $results = [
            'success' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($contacts as $contactData) {
            try {
                $dto = ErpContactSyncDTO::fromArray($contactData);
                $contact = $this->syncContact($dto);

                $results['success']++;
                $results['details'][] = [
                    'businesspartner_id' => $dto->businessPartnerId,
                    'status' => 'success',
                    'contact_id' => $contact->id
                ];
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'businesspartner_id' => $contactData['businesspartner_id'] ?? 'unknown',
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Get users that need to be synced with ERP
     */
    public function getUsersForErpSync(): array
    {
        return User::whereNull('erp_user_id')
            ->where('is_active', true)
            ->select(['id', 'name', 'email', 'department_id', 'status'])
            ->get()
            ->toArray();
    }

    /**
     * Get contacts that need to be synced with ERP
     */
    public function getContactsForErpSync(): array
    {
        return Contact::whereNull('businesspartner_id')
            ->whereNotNull('email')
            ->select(['id', 'name', 'email', 'phone', 'company', 'document'])
            ->get()
            ->toArray();
    }
}
