<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('eyebrow', 100)->nullable();
            $table->string('title', 150);
            $table->string('highlighted_title', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('primary_label', 80)->nullable();
            $table->string('primary_url', 2048)->nullable();
            $table->string('secondary_label', 80)->nullable();
            $table->string('secondary_url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('hero_slides')->insert([
            ['image_path' => '/img/carrossel1.webp', 'eyebrow' => 'Internet Residencial', 'title' => 'WiFi Ilimitada', 'highlighted_title' => 'A partir de 6 Mbps', 'description' => 'Planos para famílias, estudantes e profissionais — streaming, jogos e navegação sem interrupções. Velocidades reais, preços claros.', 'primary_label' => 'Ver Planos', 'primary_url' => '#planos', 'secondary_label' => 'Saiba Mais', 'secondary_url' => '/como-comprar', 'sort_order' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['image_path' => '/img/carrossel2.webp', 'eyebrow' => 'Soluções Empresariais', 'title' => 'Internet para', 'highlighted_title' => 'o seu negócio', 'description' => 'SLA dedicado, suporte técnico prioritário e ligações estáveis para PMEs e grandes empresas. Planos de 25 a +150 Mbps.', 'primary_label' => 'Ver Soluções', 'primary_url' => '#planos-empresariais', 'secondary_label' => 'Fale Connosco', 'secondary_url' => '/como-comprar', 'sort_order' => 2, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['image_path' => '/img/carrossel3.webp', 'eyebrow' => 'Instalação Rápida', 'title' => 'Instalamos', 'highlighted_title' => 'em até 48 horas', 'description' => 'Técnicos especializados, equipamentos incluídos e activação imediata. Sem burocracia, sem surpresas.', 'primary_label' => 'Escolher Plano', 'primary_url' => '#planos', 'secondary_label' => 'Como Funciona', 'secondary_url' => '/como-comprar', 'sort_order' => 3, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
