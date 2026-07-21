# Implémentation Flutter / Android - Ticket QR & Scanner

## 1. Dépendances requises
Ajoutez ces packages à votre `pubspec.yaml` :
```yaml
dependencies:
  qr_flutter: ^4.0.0
  mobile_scanner: ^3.2.0
  http: ^0.13.5
  crypto: ^3.0.2
```

## 2. Affichage du Ticket (Côté Passager)

### Widget `TicketScreen.dart`
Ce widget affiche le QR Code généré à partir du token reçu de l'API.

```dart
import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'dart:convert';

class TicketScreen extends StatelessWidget {
  final Map<String, dynamic> ticketData; // Données reçues de /api/tickets/{id}

  TicketScreen({required this.ticketData});

  @override
  Widget build(BuildContext context) {
    // Les données encodées dans le QR doivent correspondre à ce que le backend attend
    // Le backend envoie souvent un JSON string ou juste le token + signature
    final String qrData = ticketData['qr_code_data'] ?? jsonEncode({
      't': ticketData['token'],
      's': ticketData['signature'],
    });

    return Scaffold(
      appBar: AppBar(title: Text("Mon Ticket")),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text("Montrez ce code au chauffeur", style: TextStyle(fontSize: 18)),
            SizedBox(height: 20),
            QrImage(
              data: qrData,
              version: QrVersions.auto,
              size: 250.0,
            ),
            SizedBox(height: 20),
            Text("Expire le : ${ticketData['expires_at']}"),
            Text("Statut : ${ticketData['status']}", 
              style: TextStyle(
                color: ticketData['status'] == 'VALIDATED' ? Colors.green : Colors.black,
                fontWeight: FontWeight.bold
              )
            ),
          ],
        ),
      ),
    );
  }
}
```

## 3. Scanner de Ticket (Côté Driver / Dispatcher)

### Widget `QRScannerScreen.dart`
Ce widget permet de scanner le QR code et d'envoyer la requête de validation.

```dart
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class QRScannerScreen extends StatefulWidget {
  @override
  _QRScannerScreenState createState() => _QRScannerScreenState();
}

class _QRScannerScreenState extends State<QRScannerScreen> {
  bool isScanning = true;

  void _onDetect(BarcodeCapture capture) async {
    if (!isScanning) return;
    
    final List<Barcode> barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;

    setState(() {
      isScanning = false; // Pause scanning
    });

    final String? code = barcodes.first.rawValue;
    if (code != null) {
      await _validateTicket(code);
    }
  }

  Future<void> _validateTicket(String qrRawData) async {
    try {
      // Décoder le JSON du QR
      Map<String, dynamic> data = jsonDecode(qrRawData);
      String token = data['t'];
      String signature = data['s'];
      // double lat = ...; // Récupérer position GPS actuelle
      // double lng = ...;

      final response = await http.post(
        Uri.parse('https://picme225.com/api/scan-ticket'),
        headers: {
          'Authorization': 'Bearer YOUR_AUTH_TOKEN', // Token Driver/Dispatcher
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'token': token,
          'signature': signature,
          'lat': 5.34, // Exemple
          'lng': -4.02
        }),
      );

      final result = jsonDecode(response.body);

      if (response.statusCode == 200 && result['success'] == true) {
        _showResultDialog("Succès", "Ticket validé avec succès !", true);
      } else {
        _showResultDialog("Erreur", result['message'] ?? "Validation échouée", false);
      }
    } catch (e) {
      _showResultDialog("Erreur", "Format QR invalide ou erreur réseau", false);
    }
  }

  void _showResultDialog(String title, String message, bool success) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: Text(title, style: TextStyle(color: success ? Colors.green : Colors.red)),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              setState(() {
                isScanning = true; // Resume scanning
              });
            },
            child: Text("OK"),
          )
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Scanner Ticket")),
      body: MobileScanner(
        onDetect: _onDetect,
      ),
    );
  }
}
```

## 4. Workflow Driver

1.  Le chauffeur reçoit une notification de course assignée (via Pusher/FCM).
2.  Il accepte la course.
3.  Il se rend au point de ramassage.
4.  Il ouvre l'écran `QRScannerScreen`.
5.  Il scanne le téléphone du passager.
6.  Si succès -> L'application met à jour le statut de la course (ex: "Passenger On Board").
