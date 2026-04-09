<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MenuHelper;
use App\Helpers\Permission as PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    public function index()
    {
        $this->ensureCanManageMenu();

        $groups = MenuGroup::with(['items.children'])
            ->orderBy('position')
            ->get();

        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return view('admin.menu.index', compact('groups', 'permissions'));
    }

    public function storeGroup(Request $request)
    {
        $this->ensureCanManageMenu();

        $data = $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $position = (MenuGroup::max('position') ?? 0) + 1;

        MenuGroup::create([
            'title'     => $data['title'],
            'key'       => \Str::slug($data['title']),
            'position'  => $position,
            'is_active' => true,
        ]);

        return redirect()->route('admin.menu.index')->with('status', 'Group created.');
    }

    public function updateGroup(Request $request, MenuGroup $group)
    {
        $this->ensureCanManageMenu();

        $data = $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $group->title = $data['title'];
        $group->save();

        return redirect()->route('admin.menu.index')->with('status', 'Group updated.');
    }

    public function deleteGroup(MenuGroup $group)
    {
        $this->ensureCanManageMenu();

        $group->delete();

        return redirect()->route('admin.menu.index')->with('status', 'Group deleted.');
    }

    public function moveGroup(Request $request, MenuGroup $group)
    {
        $this->ensureCanManageMenu();

        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $query = MenuGroup::query();

        if ($direction === 'up') {
            $swap = $query->where('position', '<', $group->position)
                ->orderByDesc('position')
                ->first();
        } else {
            $swap = $query->where('position', '>', $group->position)
                ->orderBy('position')
                ->first();
        }

        if ($swap) {
            $currentPos = $group->position;
            $group->position = $swap->position;
            $swap->position = $currentPos;
            $group->save();
            $swap->save();
        }

        return redirect()->route('admin.menu.index');
    }

    public function storeItem(Request $request)
    {
        $this->ensureCanManageMenu();

        $data = $request->validate([
            'menu_group_id' => 'required|exists:menu_groups,id',
            'parent_id'     => 'nullable|exists:menu_items,id',
            'name'          => 'required|string|max:100',
            'icon'          => 'nullable|string|max:50',
            'path'          => 'nullable|string|max:255',
            'permission'    => 'nullable|string|max:100',
        ]);

        $this->assertValidMenuPath($data['path'] ?? null);

        $position = (MenuItem::where('menu_group_id', $data['menu_group_id'])
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('position') ?? 0) + 1;

        MenuItem::create([
            'menu_group_id' => $data['menu_group_id'],
            'parent_id'     => $data['parent_id'] ?? null,
            'name'          => $data['name'],
            'key'           => $data['name'],
            'icon'          => $data['icon'] ?? null,
            'path'          => $data['path'] ?? '#',
            'permission'    => $data['permission'] ?? null,
            'position'      => $position,
            'is_active'     => true,
        ]);

        return redirect()->route('admin.menu.index')->with('status', 'Menu item added.');
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $this->ensureCanManageMenu();

        $data = $request->validate([
            'name'       => 'nullable|string|max:100',
            'path'       => 'nullable|string|max:255',
            'permission' => 'nullable|string|max:100',
        ]);

        if (array_key_exists('path', $data)) {
            $this->assertValidMenuPath($data['path']);
        }

        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $item->name = $data['name'];
        }

        if (array_key_exists('path', $data) && $data['path'] !== null) {
            $item->path = $data['path'] ?: '#';
        }

        $item->permission = $data['permission'] ?? null;
        $item->save();

        return redirect()->route('admin.menu.index')->with('status', 'Menu item updated.');
    }

    public function deleteItem(MenuItem $item)
    {
        $this->ensureCanManageMenu();

        $item->delete();

        return redirect()->route('admin.menu.index')->with('status', 'Menu item deleted.');
    }

    public function moveItem(Request $request, MenuItem $item)
    {
        $this->ensureCanManageMenu();

        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $siblings = MenuItem::where('menu_group_id', $item->menu_group_id)
            ->where('parent_id', $item->parent_id);

        if ($direction === 'up') {
            $swap = $siblings->where('position', '<', $item->position)
                ->orderByDesc('position')
                ->first();
        } else {
            $swap = $siblings->where('position', '>', $item->position)
                ->orderBy('position')
                ->first();
        }

        if ($swap) {
            $currentPos = $item->position;
            $item->position = $swap->position;
            $swap->position = $currentPos;
            $item->save();
            $swap->save();
        }

        return redirect()->route('admin.menu.index');
    }

    public function moveItemGroup(Request $request, MenuItem $item)
    {
        $this->ensureCanManageMenu();

        $data = $request->validate([
            'menu_group_id' => 'required|exists:menu_groups,id',
        ]);

        $targetGroupId = (int) $data['menu_group_id'];

        // If same group, nothing to do.
        if ($targetGroupId === (int) $item->menu_group_id) {
            return redirect()->route('admin.menu.index');
        }

        // Move as a top-level item in the target group, at the end.
        $newPosition = (MenuItem::where('menu_group_id', $targetGroupId)
            ->whereNull('parent_id')
            ->max('position') ?? 0) + 1;

        $item->menu_group_id = $targetGroupId;
        $item->parent_id = null;
        $item->position = $newPosition;
        $item->save();

        return redirect()->route('admin.menu.index')->with('status', 'Menu item moved to new group.');
    }

    protected function ensureCanManageMenu(): void
    {
        if (! PermissionHelper::can(auth()->user(), 'permissions.manage')) {
            abort(403, 'You do not have permission to manage menu.');
        }
    }

    protected function assertValidMenuPath(?string $path): void
    {
        if (MenuHelper::isValidMenuPath($path)) {
            return;
        }

        throw ValidationException::withMessages([
            'path' => 'Menu path must be a valid admin URL, `#`, or a non-admin external/internal link.',
        ]);
    }
}
