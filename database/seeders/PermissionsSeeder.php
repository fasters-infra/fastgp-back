<?php

namespace Database\Seeders;

use App\Models\Permission as ModelsPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //User
        ModelsPermission::create(['name' => 'index_user', 'guard_name' => 'index_user']);
        ModelsPermission::create(['name' => 'store_user', 'guard_name' => 'store_user']);
        ModelsPermission::create(['name' => 'show_user', 'guard_name' => 'show_user']);
        ModelsPermission::create(['name' => 'update_user', 'guard_name' => 'update_user']);
        ModelsPermission::create(['name' => 'birthdays_user', 'guard_name' => 'birthdays_user']);
        ModelsPermission::create(['name' => 'updateStatus_user', 'guard_name' => 'updateStatus_user']);
        ModelsPermission::create(['name' => 'destroy_user', 'guard_name' => 'destroy_user']);

        //Tickets
        ModelsPermission::create(['name' => 'view_all_ticket', 'guard_name' => 'view_all_ticket']);
        ModelsPermission::create(['name' => 'view_ticket', 'guard_name' => 'view_ticket']);
        ModelsPermission::create(['name' => 'store_ticket', 'guard_name' => 'store_ticket']);
        ModelsPermission::create(['name' => 'show_ticket', 'guard_name' => 'show_ticket']);
        ModelsPermission::create(['name' => 'update_ticket', 'guard_name' => 'update_ticket']);
        ModelsPermission::create(['name' => 'delete_ticket', 'guard_name' => 'delete_ticket']);

        //Companies
        ModelsPermission::create(['name' => 'index_companies', 'guard_name' => 'index_companies']);
        ModelsPermission::create(['name' => 'store_companies', 'guard_name' => 'store_companies']);
        ModelsPermission::create(['name' => 'show_companies', 'guard_name' => 'show_companies']);
        ModelsPermission::create(['name' => 'update_companies', 'guard_name' => 'update_companies']);
        ModelsPermission::create(['name' => 'destroy_companies', 'guard_name' => 'destroy_companies']);

        //Smtp
        ModelsPermission::create(['name' => 'index_smtp', 'guard_name' => 'index_smtp']);
        ModelsPermission::create(['name' => 'store_smtp', 'guard_name' => 'store_smtp']);
        ModelsPermission::create(['name' => 'show_smtp', 'guard_name' => 'show_smtp']);
        ModelsPermission::create(['name' => 'update_smtp', 'guard_name' => 'update_smtp']);
        ModelsPermission::create(['name' => 'destroy_smtp', 'guard_name' => 'destroy_smtp']);

        //Cargos
        ModelsPermission::create(['name' => 'index_occupations', 'guard_name' => 'index_occupations']);
        ModelsPermission::create(['name' => 'store_occupations', 'guard_name' => 'store_occupations']);
        ModelsPermission::create(['name' => 'show_occupations', 'guard_name' => 'show_occupations']);
        ModelsPermission::create(['name' => 'update_occupations', 'guard_name' => 'update_occupations']);
        ModelsPermission::create(['name' => 'destroy_occupations', 'guard_name' => 'destroy_occupations']);

        //Níveis
        ModelsPermission::create(['name' => 'index_level', 'guard_name' => 'index_level']);
        ModelsPermission::create(['name' => 'store_level', 'guard_name' => 'store_level']);
        ModelsPermission::create(['name' => 'show_level', 'guard_name' => 'show_level']);
        ModelsPermission::create(['name' => 'update_level', 'guard_name' => 'update_level']);
        ModelsPermission::create(['name' => 'destroy_level', 'guard_name' => 'destroy_level']);

        //CargosNíveis
        ModelsPermission::create(['name' => 'index_occupation_levels', 'guard_name' => 'index_occupation_levels']);
        ModelsPermission::create(['name' => 'store_occupation_levels', 'guard_name' => 'store_occupation_levels']);
        ModelsPermission::create(['name' => 'show_occupation_levels', 'guard_name' => 'show_occupation_levels']);
        ModelsPermission::create(['name' => 'update_occupation_levels', 'guard_name' => 'update_occupation_levels']);
        ModelsPermission::create(['name' => 'destroy_occupation_levels', 'guard_name' => 'destroy_occupation_levels']);

        //Cargos do Usuário
        ModelsPermission::create(['name' => 'index_user_occupation', 'guard_name' => 'index_user_occupation']);
        ModelsPermission::create(['name' => 'store_user_occupation', 'guard_name' => 'store_user_occupation']);
        ModelsPermission::create(['name' => 'show_user_occupation', 'guard_name' => 'show_user_occupation']);
        ModelsPermission::create(['name' => 'update_user_occupation', 'guard_name' => 'update_user_occupation']);
        ModelsPermission::create(['name' => 'destroy_user_occupation', 'guard_name' => 'destroy_user_occupation']);

        //Redes Sociais
        ModelsPermission::create(['name' => 'index_social_medias', 'guard_name' => 'index_social_medias']);
        ModelsPermission::create(['name' => 'store_social_medias', 'guard_name' => 'store_social_medias']);
        ModelsPermission::create(['name' => 'show_social_medias', 'guard_name' => 'show_social_medias']);
        ModelsPermission::create(['name' => 'update_social_medias', 'guard_name' => 'update_social_medias']);
        ModelsPermission::create(['name' => 'destroy_social_medias', 'guard_name' => 'destroy_social_medias']);
        ModelsPermission::create(['name' => 'setUserSocialMedia', 'guard_name' => 'setUserSocialMedia']);
        ModelsPermission::create(['name' => 'deleteUserSocialMedia', 'guard_name' => 'deleteUserSocialMedia']);

        //Posts
        ModelsPermission::create(['name' => 'index_posts', 'guard_name' => 'index_posts']);
        ModelsPermission::create(['name' => 'store_posts', 'guard_name' => 'store_posts']);
        ModelsPermission::create(['name' => 'show_posts', 'guard_name' => 'show_posts']);
        ModelsPermission::create(['name' => 'update_posts', 'guard_name' => 'update_posts']);
        ModelsPermission::create(['name' => 'destroy_posts', 'guard_name' => 'destroy_posts']);

        //Solicitação de Horas Extras
        ModelsPermission::create(['name' => 'indexMyTeam', 'guard_name' => 'indexMyTeam']);
        ModelsPermission::create(['name' => 'myTeamChangeStatus', 'guard_name' => 'myTeamChangeStatus']);

        //Notificações
        ModelsPermission::create(['name' => 'index_notifications', 'guard_name' => 'index_notifications']);
        ModelsPermission::create(['name' => 'store_notifications', 'guard_name' => 'store_notifications']);
        ModelsPermission::create(['name' => 'show_notifications', 'guard_name' => 'show_notifications']);
        ModelsPermission::create(['name' => 'update_notifications', 'guard_name' => 'update_notifications']);
        ModelsPermission::create(['name' => 'destroy_notifications', 'guard_name' => 'destroy_notifications']);
        ModelsPermission::create(['name' => 'changeStatus', 'guard_name' => 'changeStatus']);

        //upload de documentos
        ModelsPermission::create(['name' => 'index_documents', 'guard_name' => 'index_documents']);
        ModelsPermission::create(['name' => 'show_documents', 'guard_name' => 'show_documents']);
        ModelsPermission::create(['name' => 'upload_documents', 'guard_name' => 'upload_documents']);
        ModelsPermission::create(['name' => 'download_documents', 'guard_name' => 'download_documents']);
        ModelsPermission::create(['name' => 'destroy_documents', 'guard_name' => 'destroy_documents']);

        //required labels
        ModelsPermission::create(['name' => 'index_field_required', 'guard_name' => 'index_field_required']);
        ModelsPermission::create(['name' => 'store_field_required', 'guard_name'  => 'store_field_required']);
        ModelsPermission::create(['name' => 'update_field_required', 'guard_name' => 'update_field_required']);
        ModelsPermission::create(['name' => 'destroy_field_required', 'guard_name' => 'destroy_field_required']);
    }
}
