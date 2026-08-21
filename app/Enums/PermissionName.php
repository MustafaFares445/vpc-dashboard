<?php

namespace App\Enums;

enum PermissionName: string
{
    case ClientsView = 'clients.view';
    case ClientsManage = 'clients.manage';
    case InteractionsView = 'interactions.view';
    case InteractionsCreate = 'interactions.create';
    case InteractionsUpdate = 'interactions.update';
    case InteractionsDelete = 'interactions.delete';
    case TasksView = 'tasks.view';
    case TasksCreate = 'tasks.create';
    case TasksUpdate = 'tasks.update';
    case TasksManage = 'tasks.manage';
    case TasksDelete = 'tasks.delete';
    case AccountingView = 'accounting.view';
    case AccountingManage = 'accounting.manage';
    case AuditLogsView = 'audit-logs.view';
    case ReportsView = 'reports.view';

    public function label(): string
    {
        return match ($this) {
            self::ClientsView => 'عرض العملاء',
            self::ClientsManage => 'إدارة العملاء والإسناد',
            self::InteractionsView => 'عرض المتابعات',
            self::InteractionsCreate => 'إضافة متابعة',
            self::InteractionsUpdate => 'تعديل المتابعات',
            self::InteractionsDelete => 'حذف المتابعات',
            self::TasksView => 'عرض المهام',
            self::TasksCreate => 'إنشاء المهام',
            self::TasksUpdate => 'تحديث المهام المسندة',
            self::TasksManage => 'إدارة جميع المهام والإسناد',
            self::TasksDelete => 'حذف المهام',
            self::AccountingView => 'عرض البيانات المالية',
            self::AccountingManage => 'إدارة البيانات المالية',
            self::AuditLogsView => 'عرض سجل التدقيق',
            self::ReportsView => 'عرض التقارير',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $permission): array => [$permission->value => $permission->label()])
            ->all();
    }

    public static function employeeDefaults(): array
    {
        return [
            self::ClientsView->value,
            self::InteractionsView->value,
            self::InteractionsCreate->value,
            self::InteractionsUpdate->value,
            self::TasksView->value,
            self::TasksUpdate->value,
        ];
    }
}
