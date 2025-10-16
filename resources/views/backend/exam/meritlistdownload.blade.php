<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
        .topbar {
            text-align: center;
            padding: 3rem;
        }

        table,
        td,
        th {
            border: 1px solid;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <div class="topbar">
        <h2>{{ $company->name }} </h2>
        <h4>{{ $exam->first()->exam->category }}
            @if (isset($exam->first()->exam->childcategory))
                @if ($exam->first()->exam->childcategory == '11 to 20 Grade')
                    {{ 'Teacher & Lecturer' }}
                @else
                    {{ $exam->first()->exam->childcategory }}
                @endif
            @else
                {{ ' ' }}
            @endif
            Preliminary Merit List
        </h4>
{{--        Exam date:--}}
{{--        {{ $exam->first()->exam->published_at->format('d F, Y h:i A') . ' to ' . $exam->first()->exam->expired_at->format('d F, Y h:i A') }}--}}
    </div>


    <div class="margin-top">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">SL</th>
                        <th scope="col">Merit Position</th>
                        <th scope="col">Student Name</th>
                        <th scope="col">Registration Id</th>
                        <th scope="col">Obtained Marks</th>
                        <th scope="col">Result Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $meritPosition = 0;
                        $previousScore = null;
                    @endphp
                    @foreach ($exam as $item)
                        @php
                            if ($item->obtained_marks != $previousScore) {
                               $meritPosition++;
                            }
                        @endphp
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <th scope="row">{{ $meritPosition }}</th>
                            <td>{{ $item->user->name ?? 'N/A' }}</td>
                            <td>{{ $item->user->registration_id ?? 'N/A' }}</td>
                            <td>{{ $item->obtained_marks }}</td>
                            <td>{{ $item->obtained_marks >= $find_exam->pass_marks ? 'Passed' : 'Failed' }}</td>
                        </tr>
                        @php
                            $previousScore = $item->obtained_marks;
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
