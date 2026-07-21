<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statut du Paiement</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }

        .container {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            max-width: 400px;
            width: 90%;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        h1 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        p {
            color: #666;
            margin-bottom: 2rem;
        }

        .btn {
            background: #007bff;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: transform 0.2s;
        }

        .btn:active {
            transform: scale(0.95);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">Ô£à´©Å</div>
        <h1>Paiement en cours</h1>
        <p>Votre demande de recharge (R├®f: {{ $reference }}) est en cours de traitement. Vous pouvez fermer cette page
            et retourner sur l'application.</p>
        <a href="javascript:window.close();" class="btn">Retour ├á l'application</a>
    </div>
</body>

</html>