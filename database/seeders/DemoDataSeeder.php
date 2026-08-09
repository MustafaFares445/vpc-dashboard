<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->startOfDay();

        $employees = collect([
            ['name' => 'سارة الخطيب', 'email' => 'sara.sales@example.com'],
            ['name' => 'عمر الحسن', 'email' => 'omar.accounts@example.com'],
            ['name' => 'نور منصور', 'email' => 'nour.operations@example.com'],
        ])->mapWithKeys(function (array $data): array {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Str::random(40),
                    'is_active' => true,
                ],
            );

            $user->update([
                'name' => $data['name'],
                'is_active' => true,
            ]);
            $user->syncRoles(['employee']);

            return [$data['email'] => $user];
        });

        $creatorId = User::role('admin')->value('id') ?? $employees->first()->id;

        $clients = [
            [
                'name' => 'أحمد الدرويش',
                'company_name' => 'شركة الأفق للتجارة',
                'email' => 'ahmad@alofuq.example.com',
                'phone' => '+963 900 000 101',
                'status' => 'active',
                'assigned_to' => $employees['sara.sales@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(2)->setTime(11, 30),
                'next_follow_up_at' => $now->copy()->addDays(5)->setTime(10, 0),
                'notes' => 'عميل منتظم. يفضّل التواصل صباحًا ويطلب عروض أسعار واضحة قبل تنفيذ أي طلب.',
                'created_at' => $now->copy()->subMonths(5),
            ],
            [
                'name' => 'ريم شحادة',
                'company_name' => 'مؤسسة النور للتجهيزات',
                'email' => 'reem@alnoor.example.com',
                'phone' => '+963 900 000 102',
                'status' => 'active',
                'assigned_to' => $employees['sara.sales@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(6)->setTime(14, 15),
                'next_follow_up_at' => $now->copy()->addDay()->setTime(12, 0),
                'notes' => 'مهتمة بعقد شهري للخدمات وتنتظر عرض السعر النهائي.',
                'created_at' => $now->copy()->subMonths(4),
            ],
            [
                'name' => 'سامر النجار',
                'company_name' => 'مكتب البناء الحديث',
                'email' => 'samer@modernbuild.example.com',
                'phone' => '+963 900 000 103',
                'status' => 'lead',
                'assigned_to' => $employees['nour.operations@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(1)->setTime(9, 45),
                'next_follow_up_at' => $now->copy()->addDays(2)->setTime(9, 30),
                'notes' => 'عميل محتمل جديد وصل عن طريق توصية. يحتاج متابعة بعد مراجعة العرض.',
                'created_at' => $now->copy()->subDays(18),
            ],
            [
                'name' => 'ليلى عثمان',
                'company_name' => 'مركز الريادة للاستشارات',
                'email' => 'layla@alriyada.example.com',
                'phone' => '+963 900 000 104',
                'status' => 'active',
                'assigned_to' => $employees['nour.operations@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(4)->setTime(13, 0),
                'next_follow_up_at' => $now->copy()->addDays(7)->setTime(11, 0),
                'notes' => 'تتعامل معنا بشكل ربع سنوي وتحتاج فواتير مفصلة لكل خدمة.',
                'created_at' => $now->copy()->subMonths(7),
            ],
            [
                'name' => 'محمود زيدان',
                'company_name' => 'شركة المسار للحلول',
                'email' => 'mahmoud@almasar.example.com',
                'phone' => '+963 900 000 105',
                'status' => 'lead',
                'assigned_to' => $employees['sara.sales@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(10)->setTime(16, 0),
                'next_follow_up_at' => $now->copy()->addDays(3)->setTime(15, 0),
                'notes' => 'طلب عرضًا أوليًا ولم يحسم نطاق العمل بعد.',
                'created_at' => $now->copy()->subDays(35),
            ],
            [
                'name' => 'هبة قاسم',
                'company_name' => 'دار اليسر للخدمات',
                'email' => 'hiba@alyusr.example.com',
                'phone' => '+963 900 000 106',
                'status' => 'active',
                'assigned_to' => $employees['omar.accounts@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(3)->setTime(10, 20),
                'next_follow_up_at' => $now->copy()->addDays(10)->setTime(10, 0),
                'notes' => 'عميلة ملتزمة بالدفع وتفضّل إرسال الفاتورة عبر البريد الإلكتروني.',
                'created_at' => $now->copy()->subMonths(9),
            ],
            [
                'name' => 'فراس سليمان',
                'company_name' => 'مجموعة الشام التجارية',
                'email' => 'firas@alsham.example.com',
                'phone' => '+963 900 000 107',
                'status' => 'inactive',
                'assigned_to' => $employees['sara.sales@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(55)->setTime(12, 0),
                'next_follow_up_at' => null,
                'notes' => 'تم تعليق التعامل مؤقتًا بناءً على طلب العميل.',
                'created_at' => $now->copy()->subYear(),
            ],
            [
                'name' => 'دانا يوسف',
                'company_name' => 'نقطة نمو',
                'email' => 'dana@growthpoint.example.com',
                'phone' => '+963 900 000 108',
                'status' => 'active',
                'assigned_to' => $employees['nour.operations@example.com']->id,
                'last_contact_at' => $now->copy()->subDays(8)->setTime(15, 40),
                'next_follow_up_at' => $now->copy()->addDays(4)->setTime(14, 0),
                'notes' => 'مشروع متنامٍ مع احتمالية زيادة نطاق الخدمات خلال الشهر القادم.',
                'created_at' => $now->copy()->subMonths(3),
            ],
        ];

        $clientIds = [];

        foreach ($clients as $client) {
            DB::table('clients')->updateOrInsert(
                ['email' => $client['email']],
                [
                    'name' => $client['name'],
                    'company_name' => $client['company_name'],
                    'phone' => $client['phone'],
                    'status' => $client['status'],
                    'assigned_to' => $client['assigned_to'],
                    'last_contact_at' => $client['last_contact_at'],
                    'next_follow_up_at' => $client['next_follow_up_at'],
                    'notes' => $client['notes'],
                    'created_by' => $creatorId,
                    'created_at' => $client['created_at'],
                    'updated_at' => now(),
                    'deleted_at' => null,
                ],
            );

            $clientIds[$client['email']] = DB::table('clients')
                ->where('email', $client['email'])
                ->value('id');
        }

        $interactions = [
            ['client' => 'ahmad@alofuq.example.com', 'user' => 'sara.sales@example.com', 'days_ago' => 2, 'method' => 'phone', 'note' => 'تم تأكيد استلام الدفعة الأخيرة ومناقشة احتياجات الشهر القادم.', 'follow_up_in' => 5],
            ['client' => 'reem@alnoor.example.com', 'user' => 'sara.sales@example.com', 'days_ago' => 6, 'method' => 'meeting', 'note' => 'اجتماع لمراجعة بنود العقد الشهري وتحديد نطاق الخدمة.', 'follow_up_in' => 1],
            ['client' => 'samer@modernbuild.example.com', 'user' => 'nour.operations@example.com', 'days_ago' => 1, 'method' => 'whatsapp', 'note' => 'إرسال العرض الأولي والإجابة عن أسئلة العميل حول مدة التنفيذ.', 'follow_up_in' => 2],
            ['client' => 'layla@alriyada.example.com', 'user' => 'nour.operations@example.com', 'days_ago' => 4, 'method' => 'email', 'note' => 'إرسال كشف الخدمات المنفذة والفاتورة التفصيلية للربع الحالي.', 'follow_up_in' => 7],
            ['client' => 'mahmoud@almasar.example.com', 'user' => 'sara.sales@example.com', 'days_ago' => 10, 'method' => 'phone', 'note' => 'مكالمة تعريفية لتحديد الاحتياج قبل تجهيز العرض النهائي.', 'follow_up_in' => 3],
            ['client' => 'hiba@alyusr.example.com', 'user' => 'omar.accounts@example.com', 'days_ago' => 3, 'method' => 'email', 'note' => 'إرسال الفاتورة وتأكيد موعد التحويل البنكي.', 'follow_up_in' => 10],
            ['client' => 'dana@growthpoint.example.com', 'user' => 'nour.operations@example.com', 'days_ago' => 8, 'method' => 'meeting', 'note' => 'مراجعة النتائج الحالية ومناقشة توسيع نطاق العمل للشهر القادم.', 'follow_up_in' => 4],
        ];

        foreach ($interactions as $interaction) {
            DB::table('client_interactions')->updateOrInsert(
                [
                    'client_id' => $clientIds[$interaction['client']],
                    'note' => $interaction['note'],
                ],
                [
                    'user_id' => $employees[$interaction['user']]->id,
                    'contacted_at' => $now->copy()->subDays($interaction['days_ago'])->setTime(11, 0),
                    'contact_method' => $interaction['method'],
                    'next_follow_up_at' => $now->copy()->addDays($interaction['follow_up_in'])->setTime(10, 0),
                    'created_at' => $now->copy()->subDays($interaction['days_ago']),
                    'updated_at' => now(),
                ],
            );
        }

        $tasks = [
            ['reference' => 'TASK-DEMO-001', 'title' => 'متابعة عرض سعر مؤسسة النور', 'description' => 'الاتصال بالعميلة بعد مراجعة عرض العقد الشهري والإجابة عن أي ملاحظات.', 'assignee' => 'sara.sales@example.com', 'client' => 'reem@alnoor.example.com', 'due' => 1, 'priority' => 'high', 'status' => 'pending', 'completed' => null],
            ['reference' => 'TASK-DEMO-002', 'title' => 'تجهيز كشف حساب شركة الأفق', 'description' => 'مراجعة الحركات المالية وإرسال كشف الحساب قبل اجتماع المتابعة.', 'assignee' => 'omar.accounts@example.com', 'client' => 'ahmad@alofuq.example.com', 'due' => 2, 'priority' => 'medium', 'status' => 'in_progress', 'completed' => null],
            ['reference' => 'TASK-DEMO-003', 'title' => 'تحديث خطة عمل نقطة نمو', 'description' => 'إعداد مقترح لتوسعة نطاق العمل بناءً على اجتماع العميل الأخير.', 'assignee' => 'nour.operations@example.com', 'client' => 'dana@growthpoint.example.com', 'due' => 4, 'priority' => 'high', 'status' => 'in_progress', 'completed' => null],
            ['reference' => 'TASK-DEMO-004', 'title' => 'إرسال الفاتورة الشهرية لدار اليسر', 'description' => 'التأكد من تفاصيل البنود وإرسال الفاتورة عبر البريد الإلكتروني.', 'assignee' => 'omar.accounts@example.com', 'client' => 'hiba@alyusr.example.com', 'due' => -3, 'priority' => 'medium', 'status' => 'completed', 'completed' => -3],
            ['reference' => 'TASK-DEMO-005', 'title' => 'اتصال تعريفي مع شركة المسار', 'description' => 'تثبيت نطاق الطلب قبل إعداد العرض التجاري.', 'assignee' => 'sara.sales@example.com', 'client' => 'mahmoud@almasar.example.com', 'due' => 3, 'priority' => 'urgent', 'status' => 'pending', 'completed' => null],
            ['reference' => 'TASK-DEMO-006', 'title' => 'مراجعة ملفات مركز الريادة', 'description' => 'مطابقة الخدمات المنفذة مع الفاتورة ربع السنوية.', 'assignee' => 'nour.operations@example.com', 'client' => 'layla@alriyada.example.com', 'due' => -8, 'priority' => 'low', 'status' => 'completed', 'completed' => -8],
            ['reference' => 'TASK-DEMO-007', 'title' => 'متابعة العميل المحتمل مكتب البناء الحديث', 'description' => 'الحصول على قرار العميل بخصوص العرض المرسل.', 'assignee' => 'nour.operations@example.com', 'client' => 'samer@modernbuild.example.com', 'due' => 2, 'priority' => 'high', 'status' => 'pending', 'completed' => null],
            ['reference' => 'TASK-DEMO-008', 'title' => 'أرشفة ملف مجموعة الشام', 'description' => 'أرشفة المراسلات السابقة بعد تعليق التعامل مع العميل.', 'assignee' => 'sara.sales@example.com', 'client' => 'firas@alsham.example.com', 'due' => -20, 'priority' => 'low', 'status' => 'cancelled', 'completed' => null],
        ];

        foreach ($tasks as $task) {
            DB::table('tasks')->updateOrInsert(
                ['reference' => $task['reference']],
                [
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'assigned_to' => $employees[$task['assignee']]->id,
                    'client_id' => $clientIds[$task['client']],
                    'due_at' => $now->copy()->addDays($task['due'])->setTime(15, 0),
                    'priority' => $task['priority'],
                    'status' => $task['status'],
                    'notes' => 'بيانات تشغيلية نموذجية لعرض دورة العمل داخل لوحة التحكم.',
                    'completed_at' => $task['completed'] === null ? null : $now->copy()->addDays($task['completed'])->setTime(14, 0),
                    'created_by' => $creatorId,
                    'created_at' => $now->copy()->subDays(25),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ],
            );
        }

        $invoices = [
            [
                'number' => 'INV-DEMO-1001',
                'client' => 'ahmad@alofuq.example.com',
                'issue_days_ago' => 38,
                'due_days_from_issue' => 15,
                'paid_amount' => 4250,
                'items' => [
                    ['description' => 'خدمات تشغيل وإدارة شهرية', 'quantity' => 1, 'unit_price' => 3000],
                    ['description' => 'دعم ومتابعة إضافية', 'quantity' => 5, 'unit_price' => 250],
                ],
            ],
            [
                'number' => 'INV-DEMO-1002',
                'client' => 'hiba@alyusr.example.com',
                'issue_days_ago' => 24,
                'due_days_from_issue' => 14,
                'paid_amount' => 2800,
                'items' => [
                    ['description' => 'باقة خدمات شهرية', 'quantity' => 1, 'unit_price' => 2200],
                    ['description' => 'إعداد تقارير مخصصة', 'quantity' => 3, 'unit_price' => 200],
                ],
            ],
            [
                'number' => 'INV-DEMO-1003',
                'client' => 'layla@alriyada.example.com',
                'issue_days_ago' => 15,
                'due_days_from_issue' => 20,
                'paid_amount' => 2000,
                'items' => [
                    ['description' => 'خدمات الربع الحالي', 'quantity' => 1, 'unit_price' => 5000],
                    ['description' => 'جلسات متابعة', 'quantity' => 4, 'unit_price' => 300],
                ],
            ],
            [
                'number' => 'INV-DEMO-1004',
                'client' => 'dana@growthpoint.example.com',
                'issue_days_ago' => 7,
                'due_days_from_issue' => 14,
                'paid_amount' => 0,
                'items' => [
                    ['description' => 'خدمات تطوير وتشغيل', 'quantity' => 1, 'unit_price' => 3600],
                    ['description' => 'اجتماعات متابعة', 'quantity' => 2, 'unit_price' => 250],
                ],
            ],
            [
                'number' => 'INV-DEMO-1005',
                'client' => 'reem@alnoor.example.com',
                'issue_days_ago' => 3,
                'due_days_from_issue' => 15,
                'paid_amount' => 0,
                'items' => [
                    ['description' => 'دفعة بدء العقد الشهري', 'quantity' => 1, 'unit_price' => 4500],
                ],
            ],
        ];

        $invoiceIds = [];

        foreach ($invoices as $invoice) {
            $subtotal = collect($invoice['items'])->sum(fn (array $item): float => $item['quantity'] * $item['unit_price']);
            $status = $invoice['paid_amount'] <= 0
                ? 'unpaid'
                : ($invoice['paid_amount'] >= $subtotal ? 'paid' : 'partially_paid');
            $issueDate = $now->copy()->subDays($invoice['issue_days_ago']);

            DB::table('invoices')->updateOrInsert(
                ['invoice_number' => $invoice['number']],
                [
                    'client_id' => $clientIds[$invoice['client']],
                    'issue_date' => $issueDate->toDateString(),
                    'due_date' => $issueDate->copy()->addDays($invoice['due_days_from_issue'])->toDateString(),
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'paid_amount' => $invoice['paid_amount'],
                    'notes' => 'فاتورة نموذجية مرتبطة ببيانات CRM والعمليات المالية.',
                    'created_by' => $creatorId,
                    'created_at' => $issueDate,
                    'updated_at' => now(),
                    'deleted_at' => null,
                ],
            );

            $invoiceId = DB::table('invoices')->where('invoice_number', $invoice['number'])->value('id');
            $invoiceIds[$invoice['number']] = $invoiceId;

            DB::table('invoice_items')->where('invoice_id', $invoiceId)->delete();

            DB::table('invoice_items')->insert(
                collect($invoice['items'])->values()->map(fn (array $item, int $index): array => [
                    'invoice_id' => $invoiceId,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all(),
            );
        }

        $transactions = [
            ['key' => 'تحصيل فاتورة INV-DEMO-1001', 'type' => 'income', 'days_ago' => 30, 'amount' => 4250, 'status' => 'paid', 'client' => 'ahmad@alofuq.example.com', 'invoice' => 'INV-DEMO-1001'],
            ['key' => 'تحصيل فاتورة INV-DEMO-1002', 'type' => 'income', 'days_ago' => 17, 'amount' => 2800, 'status' => 'paid', 'client' => 'hiba@alyusr.example.com', 'invoice' => 'INV-DEMO-1002'],
            ['key' => 'دفعة جزئية لفاتورة INV-DEMO-1003', 'type' => 'income', 'days_ago' => 8, 'amount' => 2000, 'status' => 'paid', 'client' => 'layla@alriyada.example.com', 'invoice' => 'INV-DEMO-1003'],
            ['key' => 'إيجار المكتب الشهري', 'type' => 'expense', 'days_ago' => 6, 'amount' => 1200, 'status' => 'paid', 'client' => null, 'invoice' => null],
            ['key' => 'اشتراكات البرامج والخدمات السحابية', 'type' => 'expense', 'days_ago' => 5, 'amount' => 420, 'status' => 'paid', 'client' => null, 'invoice' => null],
            ['key' => 'تكلفة تنفيذ مشروع نقطة نمو', 'type' => 'cost', 'days_ago' => 4, 'amount' => 950, 'status' => 'paid', 'client' => 'dana@growthpoint.example.com', 'invoice' => null],
            ['key' => 'تكلفة مواد وتشغيل لمركز الريادة', 'type' => 'cost', 'days_ago' => 11, 'amount' => 1350, 'status' => 'paid', 'client' => 'layla@alriyada.example.com', 'invoice' => null],
            ['key' => 'فاتورة خدمات إنترنت واتصالات', 'type' => 'expense', 'days_ago' => 2, 'amount' => 180, 'status' => 'pending', 'client' => null, 'invoice' => null],
        ];

        foreach ($transactions as $transaction) {
            DB::table('financial_transactions')->updateOrInsert(
                ['description' => $transaction['key']],
                [
                    'type' => $transaction['type'],
                    'date' => $now->copy()->subDays($transaction['days_ago'])->toDateString(),
                    'amount' => $transaction['amount'],
                    'payment_status' => $transaction['status'],
                    'client_id' => $transaction['client'] ? $clientIds[$transaction['client']] : null,
                    'invoice_id' => $transaction['invoice'] ? $invoiceIds[$transaction['invoice']] : null,
                    'created_by' => $creatorId,
                    'created_at' => $now->copy()->subDays($transaction['days_ago']),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ],
            );
        }

        $journalEntries = [
            [
                'reference' => 'JV-DEMO-001',
                'days_ago' => 30,
                'description' => 'تحصيل فاتورة شركة الأفق للتجارة.',
                'lines' => [
                    ['account_name' => 'الصندوق / البنك', 'debit' => 4250, 'credit' => 0, 'notes' => 'تحصيل من العميل'],
                    ['account_name' => 'إيرادات الخدمات', 'debit' => 0, 'credit' => 4250, 'notes' => 'تسوية فاتورة INV-DEMO-1001'],
                ],
            ],
            [
                'reference' => 'JV-DEMO-002',
                'days_ago' => 17,
                'description' => 'تحصيل فاتورة دار اليسر للخدمات.',
                'lines' => [
                    ['account_name' => 'الصندوق / البنك', 'debit' => 2800, 'credit' => 0, 'notes' => 'تحصيل من العميل'],
                    ['account_name' => 'إيرادات الخدمات', 'debit' => 0, 'credit' => 2800, 'notes' => 'تسوية فاتورة INV-DEMO-1002'],
                ],
            ],
            [
                'reference' => 'JV-DEMO-003',
                'days_ago' => 6,
                'description' => 'إثبات مصروف إيجار المكتب.',
                'lines' => [
                    ['account_name' => 'مصروف الإيجار', 'debit' => 1200, 'credit' => 0, 'notes' => 'إيجار الشهر الحالي'],
                    ['account_name' => 'الصندوق / البنك', 'debit' => 0, 'credit' => 1200, 'notes' => 'دفع الإيجار'],
                ],
            ],
            [
                'reference' => 'JV-DEMO-004',
                'days_ago' => 4,
                'description' => 'إثبات تكلفة تنفيذ مشروع نقطة نمو.',
                'lines' => [
                    ['account_name' => 'تكلفة الخدمات', 'debit' => 950, 'credit' => 0, 'notes' => 'تكلفة مباشرة للمشروع'],
                    ['account_name' => 'الصندوق / البنك', 'debit' => 0, 'credit' => 950, 'notes' => 'سداد التكلفة'],
                ],
            ],
            [
                'reference' => 'JV-DEMO-005',
                'days_ago' => 2,
                'description' => 'إثبات فاتورة اتصالات مستحقة وغير مدفوعة.',
                'lines' => [
                    ['account_name' => 'مصروف الاتصالات', 'debit' => 180, 'credit' => 0, 'notes' => 'فاتورة الشهر الحالي'],
                    ['account_name' => 'مصاريف مستحقة', 'debit' => 0, 'credit' => 180, 'notes' => 'مبلغ مستحق الدفع'],
                ],
            ],
        ];

        foreach ($journalEntries as $entry) {
            DB::table('journal_entries')->updateOrInsert(
                ['reference' => $entry['reference']],
                [
                    'entry_date' => $now->copy()->subDays($entry['days_ago'])->toDateString(),
                    'description' => $entry['description'],
                    'created_by' => $creatorId,
                    'created_at' => $now->copy()->subDays($entry['days_ago']),
                    'updated_at' => now(),
                ],
            );

            $entryId = DB::table('journal_entries')->where('reference', $entry['reference'])->value('id');
            DB::table('journal_entry_lines')->where('journal_entry_id', $entryId)->delete();

            DB::table('journal_entry_lines')->insert(
                collect($entry['lines'])->map(fn (array $line): array => [
                    'journal_entry_id' => $entryId,
                    'account_name' => $line['account_name'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'notes' => $line['notes'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all(),
            );
        }
    }
}
