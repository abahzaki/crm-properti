<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Estato: CRM Properti App</title>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1; /* Agar footer selalu di bawah */
            margin-top: 60px; /* Jarak untuk Navbar Fixed */
        }
        @media (min-width: 992px) {
            main, footer { margin-left: 250px; } /* Geser konten ke kanan kalau di Desktop */
        }
    </style>
    
    <?= $this->renderSection('css') ?>
</head>
<body>

    <?= $this->include('layout/navbar') ?>

    <?= $this->include('layout/sidebar') ?>

    <main class="p-4">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('layout/footer') ?>

    <?= $this->renderSection('scripts') ?>
    
</body>
</html>