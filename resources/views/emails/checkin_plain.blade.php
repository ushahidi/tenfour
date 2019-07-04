{{ $msg }}

You can view this email in a web browser at the following URL: {{ $check_in_url }}

@if (count($answers) > 0)
Please reply by writing @foreach ($answers as $answer)"{{$answer['answer']}}," @endforeach or more if you'd like to provide more detail.

Alternatively, you can...
@for ($i = 0; $i < count($answers); $i++)
* Answer {{ $answers[$i]['answer'] }} by visiting the following URL: {{ $answers[$i]['url'] }}/{{ $i }}
@endfor
@endif

Unsubscribe by visiting the following URL: {{ $unsubscribe_url }}
