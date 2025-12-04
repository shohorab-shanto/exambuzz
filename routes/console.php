<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:columns {table?} {--like=} {--exclude-system}', function () {
    $table = $this->argument('table');
    $like = $this->option('like');
    $excludeSystem = (bool) $this->option('exclude-system');
    $tables = [];
    if ($table) {
        $tables = [$table];
    } else {
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(function ($row) {
                return array_values((array) $row)[0];
            })
            ->filter(function ($t) use ($like) {
                return $like ? str_contains($t, $like) : true;
            })
            ->values()
            ->all();
    }
    if ($excludeSystem) {
        $tables = array_values(array_filter($tables, function ($t) {
            return !in_array($t, ['migrations']);
        }));
    }
    foreach ($tables as $t) {
        if (!Schema::hasTable($t)) {
            $this->warn($t . ' not found');
            continue;
        }
        $this->info('Table: ' . $t);
        $cols = DB::select('SHOW FULL COLUMNS FROM `' . $t . '`');
        foreach ($cols as $c) {
            $this->line(sprintf('%-24s %-20s %-8s %-8s %s', $c->Field, $c->Type, $c->Null, $c->Key, $c->Default));
        }
        $this->newLine();
    }
})->purpose('List columns for tables');

Artisan::command('schema:sync {--like=} {--exclude-system}', function () {
    $like = $this->option('like');
    $excludeSystem = (bool) $this->option('exclude-system');
    $expected = [
        'material_folders' => function () {
            Schema::table('material_folders', function ($table) {
                if (!Schema::hasColumn('material_folders', 'type')) $table->string('type');
                if (!Schema::hasColumn('material_folders', 'name')) $table->string('name');
                if (!Schema::hasColumn('material_folders', 'status')) $table->string('status')->nullable()->default('1');
                if (!Schema::hasColumn('material_folders', 'parent_id')) $table->unsignedBigInteger('parent_id')->nullable();
            });
        },
        'material_folder_connections' => function () {
            Schema::table('material_folder_connections', function ($table) {
                if (!Schema::hasColumn('material_folder_connections', 'material_folders_id')) $table->unsignedInteger('material_folders_id');
                if (!Schema::hasColumn('material_folder_connections', 'materials_id')) $table->unsignedInteger('materials_id');
            });
        },
        'notice_boards' => function () {
            Schema::table('notice_boards', function ($table) {
                if (!Schema::hasColumn('notice_boards', 'title')) $table->string('title');
                if (!Schema::hasColumn('notice_boards', 'description')) $table->text('description');
                if (!Schema::hasColumn('notice_boards', 'status')) $table->string('status')->default('1');
                if (!Schema::hasColumn('notice_boards', 'send_notification')) $table->boolean('send_notification')->default(true);
                if (!Schema::hasColumn('notice_boards', 'target_audience')) $table->string('target_audience')->default('all');
                if (!Schema::hasColumn('notice_boards', 'notification_sent')) $table->boolean('notification_sent')->default(false);
                if (!Schema::hasColumn('notice_boards', 'notification_sent_at')) $table->timestamp('notification_sent_at')->nullable();
            });
        },
        'revision_subjects' => function () {
            Schema::table('revision_subjects', function ($table) {
                if (!Schema::hasColumn('revision_subjects', 'name')) $table->string('name');
            });
        },
        'revision_topic_sources' => function () {
            Schema::table('revision_topic_sources', function ($table) {
                if (!Schema::hasColumn('revision_topic_sources', 'revision_subjects_id')) $table->unsignedInteger('revision_subjects_id');
                if (!Schema::hasColumn('revision_topic_sources', 'topic')) $table->string('topic');
                if (!Schema::hasColumn('revision_topic_sources', 'source')) $table->string('source');
            });
        },
        'revision_topic_questions' => function () {
            Schema::table('revision_topic_questions', function ($table) {
                if (!Schema::hasColumn('revision_topic_questions', 'revision_subjects_id')) $table->unsignedBigInteger('revision_subjects_id');
                if (!Schema::hasColumn('revision_topic_questions', 'revision_topic_source_id')) $table->unsignedBigInteger('revision_topic_source_id');
                if (!Schema::hasColumn('revision_topic_questions', 'question_name')) $table->longText('question_name')->nullable();
                if (!Schema::hasColumn('revision_topic_questions', 'question_explanation')) $table->longText('question_explanation')->nullable();
                if (!Schema::hasColumn('revision_topic_questions', 'correct')) $table->unsignedBigInteger('correct')->default(0);
                if (!Schema::hasColumn('revision_topic_questions', 'negative')) $table->unsignedBigInteger('negative')->default(0);
                if (!Schema::hasColumn('revision_topic_questions', 'empty')) $table->unsignedBigInteger('empty')->default(0);
                if (!Schema::hasColumn('revision_topic_questions', 'total')) $table->unsignedBigInteger('total')->default(0);
            });
        },
        'revision_topic_question_options' => function () {
            Schema::table('revision_topic_question_options', function ($table) {
                if (!Schema::hasColumn('revision_topic_question_options', 'revision_topic_question_id')) $table->unsignedBigInteger('revision_topic_question_id');
                if (!Schema::hasColumn('revision_topic_question_options', 'option')) $table->text('option');
                if (!Schema::hasColumn('revision_topic_question_options', 'is_answer')) $table->tinyInteger('is_answer');
            });
        },
        'revision_favorites' => function () {
            Schema::table('revision_favorites', function ($table) {
                if (!Schema::hasColumn('revision_favorites', 'user_id')) $table->unsignedBigInteger('user_id');
                if (!Schema::hasColumn('revision_favorites', 'revision_topic_question_id')) $table->unsignedBigInteger('revision_topic_question_id');
                if (!Schema::hasColumn('revision_favorites', 'is_favorite')) $table->boolean('is_favorite')->default(false);
            });
        },
        'revision_reads' => function () {
            Schema::table('revision_reads', function ($table) {
                if (!Schema::hasColumn('revision_reads', 'user_id')) $table->unsignedBigInteger('user_id');
                if (!Schema::hasColumn('revision_reads', 'revision_topic_question_id')) $table->unsignedBigInteger('revision_topic_question_id');
                if (!Schema::hasColumn('revision_reads', 'is_read')) $table->boolean('is_read')->default(false);
            });
        },
        'notifications' => function () {
            Schema::table('notifications', function ($table) {
                if (!Schema::hasColumn('notifications', 'user_id')) $table->unsignedBigInteger('user_id');
                if (!Schema::hasColumn('notifications', 'written_id')) $table->unsignedBigInteger('written_id')->nullable();
                if (!Schema::hasColumn('notifications', 'package_id')) $table->unsignedBigInteger('package_id')->nullable();
                if (!Schema::hasColumn('notifications', 'to')) $table->string('to');
                if (!Schema::hasColumn('notifications', 'name')) $table->string('name');
                if (!Schema::hasColumn('notifications', 'details')) $table->longText('details');
                if (!Schema::hasColumn('notifications', 'status')) $table->tinyInteger('status')->default(0);
                if (!Schema::hasColumn('notifications', 'read_at')) $table->timestamp('read_at')->nullable();
            });
        },
        'users' => function () {
            Schema::table('users', function ($table) {
                if (!Schema::hasColumn('users', 'fcm_token')) $table->string('fcm_token')->nullable();
                if (!Schema::hasColumn('users', 'type')) $table->string('type')->default('user');
                if (!Schema::hasColumn('users', 'status')) $table->tinyInteger('status')->default(1);
                if (!Schema::hasColumn('users', 'registration_id')) $table->string('registration_id')->nullable();
                if (!Schema::hasColumn('users', 'register_number')) $table->unsignedInteger('register_number')->nullable();
                if (!Schema::hasColumn('users', 'otp')) $table->string('otp')->nullable();
                if (!Schema::hasColumn('users', 'amount')) $table->decimal('amount', 10, 2)->nullable();
                if (!Schema::hasColumn('users', 'permission')) $table->text('permission')->nullable();
            });
        },
        'exam_questions' => function () {
            Schema::table('exam_questions', function ($table) {
                if (!Schema::hasColumn('exam_questions', 'topic_id')) $table->unsignedBigInteger('topic_id')->nullable();
            });
        },
        'written_answers' => function () {
            Schema::table('written_answers', function ($table) {
                if (!Schema::hasColumn('written_answers', 'teacher_id')) $table->unsignedBigInteger('teacher_id')->nullable();
            });
        },
        'exams' => function () {
            Schema::table('exams', function ($table) {
                if (!Schema::hasColumn('exams', 'package_id')) $table->unsignedBigInteger('package_id')->nullable();
            });
        },
        'writtens' => function () {
            Schema::table('writtens', function ($table) {
                if (!Schema::hasColumn('writtens', 'package_id')) $table->unsignedBigInteger('package_id')->nullable();
            });
        },
        'exam_question_reads' => function () {
            Schema::table('exam_question_reads', function ($table) {
                if (!Schema::hasColumn('exam_question_reads', 'user_id')) $table->unsignedBigInteger('user_id');
                if (!Schema::hasColumn('exam_question_reads', 'exam_question_id')) $table->unsignedBigInteger('exam_question_id');
                if (!Schema::hasColumn('exam_question_reads', 'is_read')) $table->boolean('is_read')->default(false);
            });
        },
        'class_routines' => function () {
            Schema::table('class_routines', function ($table) {
                if (!Schema::hasColumn('class_routines', 'type')) $table->enum('type', ['preliminary','written'])->unique();
                if (!Schema::hasColumn('class_routines', 'title')) $table->string('title')->nullable();
                if (!Schema::hasColumn('class_routines', 'pdf_file')) $table->string('pdf_file')->nullable();
                if (!Schema::hasColumn('class_routines', 'status')) $table->boolean('status')->default(1);
            });
        },
        'package_histories' => function () {
            Schema::table('package_histories', function ($table) {
                if (!Schema::hasColumn('package_histories', 'pg_transaction_id')) $table->string('pg_transaction_id')->nullable();
                if (!Schema::hasColumn('package_histories', 'approval_code')) $table->string('approval_code')->nullable();
                if (!Schema::hasColumn('package_histories', 'payment_processes')) $table->string('payment_processes')->nullable();
                if (!Schema::hasColumn('package_histories', 'bank_transaction_id')) $table->string('bank_transaction_id')->nullable();
                if (!Schema::hasColumn('package_histories', 'card_type')) $table->string('card_type')->nullable();
            });
        },
        'packages' => function () {
            Schema::table('packages', function ($table) {
                if (!Schema::hasColumn('packages', 'banner_image')) $table->string('banner_image')->nullable();
                if (!Schema::hasColumn('packages', 'published_at')) $table->date('published_at')->nullable();
                if (!Schema::hasColumn('packages', 'discount_amount')) $table->integer('discount_amount')->default(0);
            });
        },
    ];

    $added = [];
    foreach ($expected as $table => $apply) {
        if (!Schema::hasTable($table)) {
            $this->warn('Missing table: ' . $table);
            continue;
        }
        $before = collect(DB::select('SHOW FULL COLUMNS FROM `' . $table . '`'))->map(fn($c) => $c->Field)->all();
        $apply();
        $after = collect(DB::select('SHOW FULL COLUMNS FROM `' . $table . '`'))->map(fn($c) => $c->Field)->all();
        $diff = array_values(array_diff($after, $before));
        if ($diff) {
            $added[$table] = $diff;
            $this->info('Added on ' . $table . ': ' . implode(', ', $diff));
        } else {
            $this->line('No changes: ' . $table);
        }
    }

    $this->newLine();
    $this->info('Completed schema sync');

    $tables = collect(DB::select('SHOW TABLES'))
        ->map(function ($row) { return array_values((array) $row)[0]; })
        ->filter(function ($t) use ($like) { return $like ? str_contains($t, $like) : true; })
        ->values()
        ->all();
    if ($excludeSystem) {
        $tables = array_values(array_filter($tables, function ($t) {
            return !in_array($t, ['migrations']);
        }));
    }
    foreach ($tables as $t) {
        if (!Schema::hasTable($t)) { continue; }
        $this->info('Table: ' . $t);
        $cols = DB::select('SHOW FULL COLUMNS FROM `' . $t . '`');
        foreach ($cols as $c) {
            $this->line(sprintf('%-24s %-20s %-8s %-8s %s', $c->Field, $c->Type, $c->Null, $c->Key, $c->Default));
        }
        $this->newLine();
    }
})->purpose('Add missing columns for known tables');
