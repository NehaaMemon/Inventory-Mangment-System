<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index() {
        $permissions = Permission::all();
        return view('admin.backend.pages.permission.index', compact('permissions'));
    }

    public function create() {
        return view('admin.backend.pages.permission.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'group_name' => 'required',
        ]);

        Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

          $notify = array(
            'message' => 'Permission Added Successfully',
            'alert-type' => 'success'

        );

        return redirect()->route('permission.index')->with($notify);
    }

    public function edit($id) {
        $permission = Permission::findOrFail($id);
        return view('admin.backend.pages.permission.edit', compact('permission'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $id,
            'group_name' => 'required',
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

          $notify = array(
            'message' => 'Permission Updated Successfully',
            'alert-type' => 'success'

        );

        return redirect()->route('permission.index')->with($notify);
    }

    public function destroy($id) {
        $permission = Permission::findOrFail($id);
        $permission->delete();

          $notify = array(
            'message' => 'Permission Deleted Successfully',
            'alert-type' => 'success'

        );

        return redirect()->route('permission.index')->with($notify);
    }

    //Role crud methods will be here
    public function roleIndex() {
        $roles = Role::all();
        return view('admin.backend.pages.role.index', compact('roles'));
    }
    public function roleCreate() {
        return view('admin.backend.pages.role.create');
    }
    public function roleStore(Request $request) {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        Role::create([
            'name' => $request->name,
        ]);

          $notify = array(
            'message' => 'Role Added Successfully',
            'alert-type' => 'success'

        );

        return redirect()->route('role.index')->with($notify);
    }
    public function roleEdit($id) {
        $role = Role::findOrFail($id);
        return view('admin.backend.pages.role.edit', compact('role'));
    }
    public function roleUpdate(Request $request, $id) {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
        ]);
        Role::findOrFail($id)->update([
            'name' => $request->name,
        ]);

          $notify = array(
            'message' => 'Role Updated Successfully',
            'alert-type' => 'success'

        );

        return redirect()->route('role.index')->with($notify);
    }
    public function roleDestroy($id) {
        Role::findOrFail($id)->delete();

          $notify = array(
            'message' => 'Role Deleted Successfully',
            'alert-type' => 'success'

        );

        return redirect()->route('role.index')->with($notify);
    }


    /////////////////////////// Add Permission to Role Methods /////////////////
    public function addPermissionToRole() {
        $roles = Role::all();
        $permissions = Permission::all();
        $rolepermission = User::getPermissionToRole();
        return view('admin.backend.pages.role-setup.add_permission_role',
         compact('roles', 'permissions', 'rolepermission' ));
    }

    public function storePermissionToRole(Request $request) {
        $request->validate([
            'role_id' => 'required',
            'permission' => 'required',
        ]);
        // dd($request->all());

        $data = array();
        $permissions = $request->permission;
        foreach ($permissions as $key => $item) {

                $data['role_id'] = $request->role_id;
                $data['permission_id'] = $item;

                DB::table('role_has_permissions')->insert($data);
        }
          $notify = array(
            'message' => 'Permission Assigned to Role Successfully',
            'alert-type' => 'success'

        );

        return redirect()->route('all.permission.role')->with($notify);
    }

     public function allPermissionToRole() {
        $roles = Role::with('permissions')->get();
        return view('admin.backend.pages.role-setup.all_permission_role', compact('roles'));
     }

    }
