<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Digital - {{ $booking->booking_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f3f4f6;
        }

        .ticket-cut {
            clip-path: polygon(0% 0%, 100% 0%, 100% 70%, 95% 75%, 100% 80%, 100% 100%, 0% 100%, 0% 80%, 5% 75%, 0% 70%);
        }
    </style>
</head>

<body class="p-4 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
        <!-- Header -->
        <div class="bg-[#2E3192] p-6 text-center text-white">
            <h1 class="text-2xl font-bold tracking-wider">PICME PRO</h1>
            <p class="text-xs opacity-75 uppercase mt-1">Ticket de Transport Officiel</p>
        </div>

        <!-- Ticket Body -->
        <div class="p-6 relative">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Référence</p>
                    <p class="text-lg font-bold text-gray-800">#{{ $booking->booking_id }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 uppercase font-semibold">Date</p>
                    <p class="font-bold text-gray-800">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <!-- Route Info -->
            <div
                class="bg-gray-50 rounded-2xl p-4 flex items-center justify-between mb-8 border border-dashed border-gray-200">
                <div class="text-center flex-1">
                    <p class="text-xs text-gray-400 font-bold uppercase">Départ</p>
                    <p class="text-lg font-bold text-[#2E3192]">{{ $booking->s_address }}</p>
                </div>
                <div class="px-4">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </div>
                <div class="text-center flex-1">
                    <p class="text-xs text-gray-400 font-bold uppercase">Arrivée</p>
                    <p class="text-lg font-bold text-[#2E3192]">{{ $booking->d_address }}</p>
                </div>
            </div>

            <!-- Passenger Info -->
            <div class="space-y-4 mb-8">
                <div class="flex justify-between">
                    <span class="text-gray-500">Passager :</span>
                    <span class="font-bold text-gray-800">{{ $booking->user->first_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Places :</span>
                    <span class="font-bold text-gray-800">{{ $booking->seat_count }} Siège(s)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-100 bg-[#2E3192] px-2 py-1 rounded text-xs">Paiement : CASH</span>
                    <span
                        class="text-xl font-bold text-[#2ECC71]">{{ number_format($booking->payment->total, 0, ',', ' ') }}
                        FCFA</span>
                </div>
            </div>

            <!-- QR Code Section -->
            <div
                class="flex flex-col items-center justify-center p-6 bg-[#f9fafb] rounded-3xl border-2 border-gray-100 italic">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrData) }}"
                    alt="Validation QR" class="w-32 h-32 mb-4">
                <p class="text-[10px] text-gray-400 text-center uppercase tracking-widest">Scannez ce code au départ
                    pour valider votre voyage</p>

                @if($booking->ticket && $booking->ticket->status == 'VALIDATED')
                    <div
                        class="mt-4 bg-green-100 text-green-700 px-4 py-1 rounded-full text-xs font-bold flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        TICKET DÉJÀ VALIDÉ
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="p-6 bg-gray-50 text-center border-t border-gray-100">
            <p class="text-[10px] text-gray-400 leading-tight">Merci de voyager avec la compagnie
                {{ $booking->fleet ? $booking->fleet->name : 'Partenaire PICME' }}.<br>Ce ticket est personnel et non
                transférable.</p>
        </div>
    </div>
</body>

</html>