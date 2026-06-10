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

        public function editPermissionToRole($id) {
            $role = Role::findOrFail($id);
            $rolepermission = User::getPermissionToRole();

            return view('admin.backend.pages.role-setup.edit_permission_role',
                compact('role', 'rolepermission'));
        }

        public function updatePermissionToRole(Request $request, $id) {
            $request->validate([
                'permission' => 'nullable|array',
                'permission.*' => 'exists:permissions,id',
            ]);

            $role = Role::findOrFail($id);
            $permissions = Permission::whereIn('id', $request->permission ?? [])
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($permissions);

            $notify = array(
                'message' => 'Permission Updated to Role Successfully',
                'alert-type' => 'success'

            );

            return redirect()->route('all.permission.role')->with($notify);
        }
        public function destroyPermissionToRole($id) {
            $role = Role::findOrFail($id);
            $role->permissions()->detach();
            $role->delete();

            $notify = array(
                'message' => 'Permission Deleted from Role Successfully',
                'alert-type' => 'success'

            );

            return redirect()->route('all.permission.role')->with($notify);
        }

        ////////////////////////////////////Admin User Role Setup Methods //////////////////////////////
        public function allAdmin() {
            $allAdmins = User::where('role', 'admin')->latest()->get();         
            return view('admin.backend.pages.admin-role.index',
             compact('allAdmins'));   
        }

        public function addAdmin() {
                $roles = Role::all();
            return view('admin.backend.pages.admin-role.create', compact('roles'));
        }

                public function storeAdmin(Request $request) {
                    $request->validate([
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email',
                        'password' => 'required|min:6',
                      
                    ]);
    
                    User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => bcrypt($request->password),
                        'role' => 'admin',
                    ]);
                    
                    if($request->role_id) {
                        $role = Role::findOrFail($request->role_id);
                        $user = User::where('email', $request->email)->first();
                        $user->assignRole($role->name);
                    }
                    
                    $notify = array(
                        'message' => 'Admin User Added Successfully',
                        'alert-type' => 'success'
        
                    );
        
                    return redirect()->route('admin.index')->with($notify);
                }

            public function destroyAdmin($id) {
                User::findOrFail($id)->delete();
    
                $notify = array(
                    'message' => 'Admin User Deleted Successfully',
                    'alert-type' => 'success'
    
                );
    
                return redirect()->route('admin.index')->with($notify);
            }
    
            public function editAdmin($id) {
                $admin = User::findOrFail($id);
                $roles = Role::all();
                return view('admin.backend.pages.admin-role.edit', compact('admin', 'roles'));
            }
    
            public function updateAdmin(Request $request, $id) {
                $request->validate([
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email,' . $id,
                    'password' => 'nullable|min:6',
                    'role_id' => 'nullable|exists:roles,id',
                ]);

                $user = User::findOrFail($id);
                $data = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'role' => 'admin',
                ];

                if ($request->filled('password')) {
                    $data['password'] = bcrypt($request->password);
                }

                $user->update($data);

                if ($request->role_id) {
                    $role = Role::findOrFail($request->role_id);
                    $user->syncRoles($role->name);
                } else {
                    $user->syncRoles([]);
                }
    
                $notify = array(
                    'message' => 'Admin User Updated Successfully',
                    'alert-type' => 'success'
    
                );
    
                return redirect()->route('admin.index')->with($notify);
            }

    }
