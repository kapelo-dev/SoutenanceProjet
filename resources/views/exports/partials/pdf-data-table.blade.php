<table class="pdf-table">
    <thead>
        <tr>
            @foreach($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($data as $row)
            <tr>
                @foreach($row as $cell)
                    <td>
                        @if(is_array($cell) && isset($cell['libelle']))
                            <span class="pdf-op-cell">
                                @if(!empty($cell['logo']['base64']))
                                    <img src="{{ $cell['logo']['base64'] }}" alt="" class="pdf-op-logo" />
                                @elseif(!empty($cell['logo']['couleur']))
                                    <span class="pdf-op-badge" style="background-color: {{ $cell['logo']['couleur'] }};">
                                        {{ $cell['logo']['code'] ?? 'OP' }}
                                    </span>
                                @endif
                                {{ $cell['libelle'] }}
                            </span>
                        @elseif(is_array($cell) && isset($cell['badge']))
                            <span class="pdf-badge {{ $cell['class'] ?? 'pdf-badge-neutral' }}">{{ $cell['badge'] }}</span>
                        @else
                            {{ $cell }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($headers) }}" class="pdf-empty">
                    {{ $emptyMessage ?? 'Aucune donnée à exporter' }}
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
