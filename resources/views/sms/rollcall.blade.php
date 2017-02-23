{{ $msg }}
Reply with<?php if ($keyword): ?> "{{$keyword}}" and include<?php endif; ?> @foreach ($answers as $answer)"{{$answer}}"<?php if ($loop->remaining == 1): ?> or <?php elseif (!$loop->last): ?>, <?php endif; ?>@endforeach in your response, or go to: {{ $rollcall_link }}
