@extends('admin.layout.base')

@section('title', 'Gestion des Itinéraires PDP')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h5 class="mb-1">Importer des Itinéraires PDP (JSON)</h5>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('admin.pdp.routes.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="json_file">Fichier JSON des Itinéraires</label>
                        <input type="file" class="form-control" id="json_file" name="json_file" accept=".json" required>
                        <small class="form-text text-muted">
                            Téléchargez un fichier JSON contenant les itinéraires au format spécifié ci-dessous.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="update_existing" value="1">
                            Mettre à jour les itinéraires existants (par nom)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"></i> Importer les Itinéraires
                    </button>
                </form>

                <hr>

                <h6>Structure JSON Requise</h6>
                <pre class="bg-light p-3"><code>[
      {
        "name": "LIGNE 1 EXEMPLE",
        "description": "Description de la ligne",
        "type": "COMMUNAL",
        "status": "APPROVED",
        "stops": [
          {
            "name": "Arrêt 1",
            "address": "Adresse complète",
            "latitude": 5.346746,
            "longitude": -3.995813,
            "type": "gare",
            "order": 1
          },
          {
            "name": "Arrêt 2",
            "address": "Adresse complète",
            "latitude": 5.352054,
            "longitude": -3.991244,
            "type": "arret",
            "order": 2
          }
        ],
        "segments": [
          {
            "from_stop_order": 1,
            "to_stop_order": 2,
            "price": 200,
            "distance_km": 1.2
          }
        ]
      }
    ]</code></pre>

                <div class="alert alert-info mt-3">
                    <strong>Notes importantes :</strong>
                    <ul>
                        <li><code>type</code> : "COMMUNAL" ou "INTER_COMMUNAL"</li>
                        <li><code>status</code> : "PROPOSED", "VOTING", "APPROVED", ou "REJECTED"</li>
                        <li><code>type</code> d'arrêt : "gare" (point de départ) ou "arret"</li>
                        <li><code>price</code> : Prix en FCFA pour le segment</li>
                        <li><code>distance_km</code> : Distance en kilomètres</li>
                        <li>Les <code>order</code> doivent être séquentiels (1, 2, 3...)</li>
                    </ul>
                </div>
            </div>

            <div class="box box-block bg-white mt-3">
                <h5 class="mb-1">Itinéraires Actuels</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Arrêts</th>
                            <th>Segments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routes as $route)
                            <tr>
                                <td>{{ $route->id }}</td>
                                <td>{{ $route->name }}</td>
                                <td><span class="badge badge-info">{{ $route->type }}</span></td>
                                <td><span
                                        class="badge badge-{{ $route->status == 'APPROVED' ? 'success' : 'warning' }}">{{ $route->status }}</span>
                                </td>
                                <td>{{ $route->stops->count() }}</td>
                                <td>{{ $route->segments->count() }}</td>
                                <td>
                                    <form action="{{ route('admin.pdp.routes.delete', $route->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Êtes-vous sûr ?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection