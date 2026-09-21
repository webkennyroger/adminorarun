<?php

namespace App\Http\Controllers\Api;

use App\Events\SaveToggled;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\SavedItem;
use App\Traits\ResolvesActivityItems;
use Illuminate\Http\Request;

class SaveController extends Controller
{
    use ResolvesActivityItems;

    /**
     * Toggle save on an item (post, poll, activity).
     */
    public function toggleSave(Request $request, $id)
    {
        $item = $this->resolveItem($id);
        $user = $request->user();

        $existingSave = SavedItem::where('user_id', $user->id)
            ->where('saved_item_id', $item->id)
            ->where('saved_item_type', get_class($item))
            ->first();

        if ($existingSave) {
            $existingSave->delete();
            $isSaved = false;
        } else {
            SavedItem::create([
                'user_id' => $user->id,
                'saved_item_id' => $item->id,
                'saved_item_type' => get_class($item),
            ]);
            $isSaved = true;
        }

        // Determinar o ID prefixado correto para o broadcast
        $prefixedId = $id;
        if (! is_string($id) || ! str_contains($id, '_')) {
            $prefix = ($item instanceof Activity) ? 'activity_' : (($item->type === 'poll') ? 'poll_' : 'post_');
            $prefixedId = $prefix.$item->id;
        }

        event(new SaveToggled($prefixedId, $isSaved, $user->id));

        return response()->json([
            'success' => true,
            'is_saved' => $isSaved,
        ]);
    }
}
