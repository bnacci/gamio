<style>
    * {
        margin: 0;
        padding: 0;
        border: none;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    thead {
        background-color: #007BFF;
        color: white;
    }

    th,
    td {
        padding: 12px 15px;
        text-align: center;
    }

    th {
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    tbody tr {
        transition: background-color 0.3s;
    }

    tbody tr:hover {
        background-color: #f1f1f1;
    }

    td img {
        object-fit: cover;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        margin-right: 5px;
        border: 2px solid #fff;
        box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
        margin-right: -18px;
    }

    .name-link {
        color: #000;
    }

    .rank {
        font-weight: bold;
        color: #FF8C00;
    }

    .level {
        color: #28a745;
    }

    .xp {
        color: #17a2b8;
    }

    .badges {
        display: flex;
        margin-top: 5px;
    }

    /* Styling for links */
    .rank-link,
    .name-link {
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s;
    }

    .rank-link:hover,
    .name-link:hover {
        color: #007BFF;
    }
</style>

<table>
    <thead>
        <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>Level</th>
            <th>XP</th>
            <th>Badges</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($leaderboard as $key => $leader)
            <tr>
                <td class="rank">
                    <a href="#0" class="rank-link">{{ $leader->rank }}</a>
                </td>
                <td>
                    <a href="#0" class="name-link">{{ $leader->name }}</a>
                </td>
                <td class="level">{{ $leader->level }}</td>
                <td class="xp">{{ \Number::format($leader->total_xp) }}</td>
                <td class="badges">
                    @foreach ($leader->badges as $leaderBadge)
                        @if ($leaderBadge->icon)
                            <img src="{{ $leaderBadge->icon }}" alt="Badge">
                        @else
                            <span>{{ $leaderBadge->badge_id }}</span>
                        @endif
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
