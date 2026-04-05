<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Dashboard sondages</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                color: #0f172a;
                font-size: 12px;
                margin: 24px;
            }

            h1, h2, h3, p {
                margin: 0;
            }

            .header {
                margin-bottom: 24px;
                padding-bottom: 16px;
                border-bottom: 1px solid #cbd5e1;
            }

            .muted {
                color: #64748b;
            }

            .grid {
                width: 100%;
                margin: 18px 0 24px;
            }

            .card {
                display: inline-block;
                width: 22.8%;
                margin-right: 2%;
                padding: 14px;
                border: 1px solid #dbeafe;
                border-radius: 12px;
                background: #f8fbff;
                vertical-align: top;
                box-sizing: border-box;
            }

            .card:last-child {
                margin-right: 0;
            }

            .value {
                margin-top: 8px;
                font-size: 22px;
                font-weight: bold;
            }

            .section {
                margin-top: 24px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 12px;
            }

            th,
            td {
                border-bottom: 1px solid #e2e8f0;
                padding: 10px 8px;
                text-align: left;
                vertical-align: top;
            }

            th {
                background: #f8fafc;
                color: #334155;
                font-size: 11px;
                text-transform: uppercase;
            }

            .badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 999px;
                background: #e0f2fe;
                color: #0369a1;
                font-size: 11px;
            }

            .footer {
                margin-top: 24px;
                font-size: 10px;
                color: #64748b;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Dashboard des sondages</h1>
            <p class="muted" style="margin-top: 6px;">Utilisateur : {{ $user->name }} · Période : {{ $rangeLabel }} · Généré le {{ now()->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }}</p>
        </div>

        <div class="grid">
            <div class="card">
                <p class="muted">Sondages créés</p>
                <p class="value">{{ $dashboardStats['totalPolls'] }}</p>
            </div>
            <div class="card">
                <p class="muted">Réponses collectées</p>
                <p class="value">{{ $dashboardStats['totalResponses'] }}</p>
            </div>
            <div class="card">
                <p class="muted">Dates proposées</p>
                <p class="value">{{ $dashboardStats['totalDates'] }}</p>
            </div>
            <div class="card">
                <p class="muted">Moyenne de réponses</p>
                <p class="value">{{ $dashboardStats['averageResponses'] }}</p>
            </div>
        </div>

        <div class="section">
            <h2>Répartition</h2>
            <p class="muted" style="margin-top: 6px;">Vote unique : {{ $dashboardStats['singleChoicePolls'] }} · Vote multiple : {{ $dashboardStats['multipleChoicePolls'] }}</p>
            @if ($dashboardStats['topPoll'])
                <p style="margin-top: 8px;">Sondage le plus performant : <strong>{{ $dashboardStats['topPoll']->title }}</strong> ({{ $dashboardStats['topPoll']->responses_count }} réponse(s))</p>
            @endif
        </div>

        <div class="section">
            <h2>Évolution des réponses</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Réponses</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trendPoints as $point)
                        <tr>
                            <td>{{ $point['label'] }}</td>
                            <td>{{ $point['value'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Aucune donnée pour cette période.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Gestion détaillée</h2>
            <table>
                <thead>
                    <tr>
                        <th>Sondage</th>
                        <th>Mode</th>
                        <th>Dates</th>
                        <th>Réponses</th>
                        <th>Créé</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($myPolls as $poll)
                        <tr>
                            <td>
                                <strong>{{ $poll->title }}</strong>
                                @if ($poll->description)
                                    <div class="muted" style="margin-top: 4px;">{{ $poll->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge">{{ $poll->allows_multiple_choices ? 'Vote multiple' : 'Vote unique' }}</span>
                            </td>
                            <td>{{ $poll->dates_count }}</td>
                            <td>{{ $poll->responses_count }}</td>
                            <td>{{ $poll->created_at->locale('fr')->isoFormat('D MMM YYYY') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Aucun sondage disponible.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            Rapport exporté depuis l’application de sondage de date.
        </div>
    </body>
</html>
