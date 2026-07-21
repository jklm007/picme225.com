# Guide d'Implémentation Android (Flutter) : DAO, Token ECO & Mobile Money

Ce guide détaille les widgets et services Dart nécessaires pour intégrer les fonctionnalités DAO, Token ECO et Mobile Money dans l'application mobile Picme225.

## 1. Modèles de Données (Dart)

Créez un fichier `models/dao_token_models.dart`.

```dart
class DaoProposal {
  final int id;
  final String title;
  final String description;
  final String type;
  final String status;
  final DateTime endTime;
  final int votesFor;
  final int votesAgainst;
  final int votesAbstain;

  DaoProposal({
    required this.id,
    required this.title,
    required this.description,
    required this.type,
    required this.status,
    required this.endTime,
    required this.votesFor,
    required this.votesAgainst,
    required this.votesAbstain,
  });

  factory DaoProposal.fromJson(Map<String, dynamic> json) {
    return DaoProposal(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      type: json['type'],
      status: json['status'],
      endTime: DateTime.parse(json['end_time']),
      votesFor: json['votes_for'] ?? 0,
      votesAgainst: json['votes_against'] ?? 0,
      votesAbstain: json['votes_abstain'] ?? 0,
    );
  }
}

class EcoTransaction {
  final int id;
  final String type;
  final double amount;
  final String status;
  final DateTime date;

  EcoTransaction({required this.id, required this.type, required this.amount, required this.status, required this.date});

  factory EcoTransaction.fromJson(Map<String, dynamic> json) {
    return EcoTransaction(
      id: json['id'],
      type: json['type'],
      amount: double.parse(json['amount'].toString()),
      status: json['status'],
      date: DateTime.parse(json['created_at']),
    );
  }
}
```

## 2. Services API

Créez un fichier `services/web3_payment_service.dart`.

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/dao_token_models.dart';

class Web3PaymentService {
  final String baseUrl = "https://picme225.com/api";
  final String token; // Auth Bearer Token

  Web3PaymentService(this.token);

  // --- DAO ---
  Future<List<DaoProposal>> getProposals() async {
    final response = await http.get(
      Uri.parse('$baseUrl/dao/proposals'),
      headers: {'Authorization': 'Bearer $token'},
    );
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body)['data'] as List;
      return data.map((e) => DaoProposal.fromJson(e)).toList();
    }
    throw Exception('Failed to load proposals');
  }

  Future<void> vote(int proposalId, String voteType) async {
    final response = await http.post(
      Uri.parse('$baseUrl/dao/proposals/$proposalId/vote'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json'
      },
      body: jsonEncode({'vote': voteType}), // FOR, AGAINST, ABSTAIN
    );
    if (response.statusCode != 201) {
      throw Exception('Vote failed: ${response.body}');
    }
  }

  // --- ECO TOKEN ---
  Future<Map<String, dynamic>> getBalance() async {
    final response = await http.get(
      Uri.parse('$baseUrl/eco-token/balance'),
      headers: {'Authorization': 'Bearer $token'},
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Failed to load balance');
  }

  Future<void> transferToken(String toAddress, double amount) async {
    final response = await http.post(
      Uri.parse('$baseUrl/eco-token/transfer'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json'
      },
      body: jsonEncode({'to_wallet_address': toAddress, 'amount': amount}),
    );
    if (response.statusCode != 200) {
      throw Exception('Transfer failed');
    }
  }

  // --- MOBILE MONEY ---
  Future<void> initiateMobileMoneyPayment(String provider, String phone, double amount, String reference) async {
    final response = await http.post(
      Uri.parse('$baseUrl/mobile-money/payment/initiate'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json'
      },
      body: jsonEncode({
        'provider': provider, // orange, mtn, moov
        'phone_number': phone,
        'amount': amount,
        'reference': reference,
        'type': 'WALLET_RECHARGE'
      }),
    );
    if (response.statusCode != 201) {
      throw Exception('Payment initiation failed');
    }
  }
}
```

## 3. Écrans (Widgets)

### A. Écran DAO (Gouvernance)

```dart
class DaoScreen extends StatefulWidget {
  @override
  _DaoScreenState createState() => _DaoScreenState();
}

class _DaoScreenState extends State<DaoScreen> {
  late Web3PaymentService _service;
  List<DaoProposal> _proposals = [];

  @override
  void initState() {
    super.initState();
    _service = Web3PaymentService("YOUR_AUTH_TOKEN");
    _loadProposals();
  }

  _loadProposals() async {
    try {
      var data = await _service.getProposals();
      setState(() => _proposals = data);
    } catch (e) {
      print(e);
    }
  }

  _vote(int id, String type) async {
    try {
      await _service.vote(id, type);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Vote enregistré !")));
      _loadProposals();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Erreur vote")));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Gouvernance DAO")),
      body: ListView.builder(
        itemCount: _proposals.length,
        itemBuilder: (ctx, i) {
          final p = _proposals[i];
          return Card(
            margin: EdgeInsets.all(10),
            child: Padding(
              padding: EdgeInsets.all(15),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(p.title, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  SizedBox(height: 5),
                  Text(p.description),
                  SizedBox(height: 10),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      ElevatedButton(onPressed: () => _vote(p.id, 'FOR'), child: Text("POUR (${p.votesFor})"), style: ElevatedButton.styleFrom(backgroundColor: Colors.green)),
                      ElevatedButton(onPressed: () => _vote(p.id, 'AGAINST'), child: Text("CONTRE (${p.votesAgainst})"), style: ElevatedButton.styleFrom(backgroundColor: Colors.red)),
                    ],
                  )
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
```

### B. Écran Portefeuille ECO Token

```dart
class EcoWalletScreen extends StatefulWidget {
  @override
  _EcoWalletScreenState createState() => _EcoWalletScreenState();
}

class _EcoWalletScreenState extends State<EcoWalletScreen> {
  double balance = 0.0;
  String address = "...";

  @override
  void initState() {
    super.initState();
    _loadBalance();
  }

  _loadBalance() async {
    var service = Web3PaymentService("YOUR_AUTH_TOKEN");
    var data = await service.getBalance();
    setState(() {
      balance = double.parse(data['balance'].toString());
      address = data['wallet_address'] ?? "Non défini";
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Mon Portefeuille ECO")),
      body: Column(
        children: [
          Container(
            padding: EdgeInsets.all(20),
            color: Colors.blueAccent,
            width: double.infinity,
            child: Column(
              children: [
                Text("Solde Actuel", style: TextStyle(color: Colors.white)),
                Text("$balance ECO", style: TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold)),
                SizedBox(height: 10),
                Text("Adresse: $address", style: TextStyle(color: Colors.white70, fontSize: 12)),
              ],
            ),
          ),
          ListTile(
            leading: Icon(Icons.send),
            title: Text("Envoyer des tokens"),
            onTap: () {
              // Ouvrir dialog de transfert
            },
          ),
          ListTile(
            leading: Icon(Icons.history),
            title: Text("Historique"),
            onTap: () {
              // Naviguer vers historique
            },
          )
        ],
      ),
    );
  }
}
```

### C. Écran Paiement Mobile Money

```dart
class MobileMoneyScreen extends StatelessWidget {
  final TextEditingController phoneCtrl = TextEditingController();
  final TextEditingController amountCtrl = TextEditingController();

  _pay(BuildContext context, String provider) async {
    try {
      var service = Web3PaymentService("YOUR_AUTH_TOKEN");
      await service.initiateMobileMoneyPayment(
        provider,
        phoneCtrl.text,
        double.parse(amountCtrl.text),
        "REF-${DateTime.now().millisecondsSinceEpoch}"
      );
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Paiement initié ! Vérifiez votre téléphone.")));
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Erreur: $e")));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Recharge Mobile Money")),
      body: Padding(
        padding: EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(controller: phoneCtrl, decoration: InputDecoration(labelText: "Numéro de téléphone (10 chiffres)")),
            TextField(controller: amountCtrl, decoration: InputDecoration(labelText: "Montant (FCFA)")),
            SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _providerBtn(context, "orange", Colors.orange),
                _providerBtn(context, "mtn", Colors.yellow[700]!),
                _providerBtn(context, "moov", Colors.blue),
              ],
            )
          ],
        ),
      ),
    );
  }

  Widget _providerBtn(BuildContext context, String provider, Color color) {
    return ElevatedButton(
      onPressed: () => _pay(context, provider),
      child: Text(provider.toUpperCase()),
      style: ElevatedButton.styleFrom(backgroundColor: color),
    );
  }
}
```
