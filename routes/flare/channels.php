<?php

// When a character updates, up their character sheet.
Broadcast::channel('update-character-sheet-{userId}', function ($user, $userId) {
	return $user->id === (int) $userId;
});