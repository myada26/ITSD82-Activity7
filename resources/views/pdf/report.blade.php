<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} — {{ $org->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #0f1f17; background: white; }

        .page { padding: 24px 28px; }

        .header { border-bottom: 2px solid #1a7a41; padding-bottom: 12px; margin-bottom: 16px; }
        .header .org { font-size: 14px; font-weight: bold; color: #0d4a1e; }
        .header .sub { font-size: 10px; color: #4a6356; margin-top: 2px; }
        .header .title { font-size: 16px; font-weight: bold; color: #1a7a41; margin-top: 8px; }
        .header .period { font-size: 10px; color: #8aa89a; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th {
            background: #e8f4ed;
            color: #0d4a1e;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 10px;
            text-align: left;
            border-bottom: 1px solid #b3e5c9;
        }
        td {
            padding: 6px 10px;
            font-size: 10px;
            color: #0f1f17;
            border-bottom: 1px solid #eaf0ec;
        }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #f8fbf9; }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dde8e1;
            font-size: 9px;
            color: #8aa89a;
            display: flex;
            justify-content: space-between;
        }

        .empty { text-align: center; padding: 32px; color: #8aa89a; font-size: 11px; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="org">{{ $org->name }}</div>
        <div class="sub">Fee Collection and Tracking System (FCATS)</div>
        <div class="title">{{ $reportTitle }}</div>
        <div class="period">{{ $semester->name }} &nbsp;·&nbsp; Generated {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    @if(count($reportData) > 0)
    <table>
        <thead>
            <tr>
                @foreach($reportColumns as $col)
                <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty">No records found for the selected parameters.</div>
    @endif

    <div class="footer">
        <span>{{ $org->name }} &mdash; FCATS Report</span>
        <span>{{ now()->format('Y-m-d H:i') }}</span>
    </div>

</div>
</body>
</html>
