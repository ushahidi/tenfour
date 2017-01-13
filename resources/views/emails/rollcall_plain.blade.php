{{ $msg }}

You can view this email in a web browser at the following URL: {{ $roll_call_url }}

@if (count($answers) > 0)
Please reply by writing "{{ $answers[1] }}," "{{ $answers[0] }}," or more if you'd like to provide more detail.

Alternatively, you can...
* Answer {{ $answers[1] }} by visiting the following URL: {{ $answer_url_yes }}
* Answer {{ $answers[0] }} by visiting the following URL: {{ $answer_url_no }}
@else
Please reply on Rollcall: {{ $answer_url }} or by replying directly to this email
@endif
