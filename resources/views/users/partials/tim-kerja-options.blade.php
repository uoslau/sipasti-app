@php
    $selected = $selected ?? [];
@endphp
<div class="users-team-grid">
    @foreach ($tim_kerja as $tk)
        <label class="users-team-option" for="tim_kerja_{{ $tk->id }}">
            <input class="form-check-input" type="checkbox" name="tim_kerja_ids[]" id="tim_kerja_{{ $tk->id }}"
                value="{{ $tk->id }}" {{ in_array($tk->id, $selected) ? 'checked' : '' }} />
            <span>
                <span class="users-team-alias">{{ $tk->alias_tim_kerja }}</span>
                <span class="users-team-name">{{ $tk->nama_tim_kerja }}</span>
            </span>
        </label>
    @endforeach
</div>
