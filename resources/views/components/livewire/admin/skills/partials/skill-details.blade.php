<div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="skill-name">Name: </label>
                <input type="text" class="form-control required" id="skill-name" name="name" wire:model="skill.name">
                @error('skill.name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="skill-max_level">Max level: </label>
                <input type="number" class="form-control required" id="skill-max_level" name="max_level" wire:model="skill.max_level">
                @error('skill.max_level') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="skill-description">Description: </label>
                <textarea class="form-control required" name="skill-description" wire:model="skill.description"></textarea>
                @error('skill.description') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <div class="form-group form-check-inline">
                <input type="checkbox" class="form-check-input" id="monster-can-have-skill" wire:model="skill.can_monsters_have_skill">
                <label class="form-check-label" for="monster-can-have-skill">Can monsters have this skill?</label>
            </div>
        </div>
    </div>
</div>
