{{ $msg }}

You can view this email in a web browser at the following URL: {{ $roll_call_url }}

@if (count($answers) > 0)
Please reply by writing @foreach ($answers as $answer)"{{$answer}}," @endforeach or more if you'd like to provide more detail.

Alternatively, you can...
@for ($i = 0; $i < count($answers); $i++)
* Answer {{ $answers[$i] }} by visiting the following URL: {{ $answer_url }}/{{ $i }}
@endfor
@else
Please reply on Rollcall: {{ $reply_url }} or by replying directly to this email
@endif

Unsubscribe by visiting the following URL: {{ $unsubscribe_url }}
