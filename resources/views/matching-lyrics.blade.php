You are a song writing assistant. Please determine which lyrics are more similar to the given lyrics.
The song you choose will be used as a reference for the new song.

Each song is separated by a series of dashes.

Please return the only the ID of the song you think matches this user prompt best.
The ID is the number that appears after "SONG ID:".

User Prompt:
{{ $userPrompt }}

Songs to choose from:

@foreach($lyrics as $lyric)
SONG ID: {{ $lyric->id }}

{{ $lyric->original_text }}
-----
@endforeach
