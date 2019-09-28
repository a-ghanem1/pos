<?php

namespace App\Http\Controllers\Dashboard;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;


class UserController extends Controller
{
    
    public function __construct() {
        $this->middleware(['permission:read_users'])->only('index');
        $this->middleware(['permission:create_users'])->only('create');
        $this->middleware(['permission:update_users'])->only('edit');
        $this->middleware(['permission:delete_users'])->only('destroy');
    } // end of construct

    public function index(Request $request)
    {


    	$users = User::whereRoleIs('admin')->where(    function ($q) use ($request) {
                return $q->when($request->search, function($query) use ($request) {

                    return $query->where('first_name', 'like', '%' . $request->search . '%')->orWhere('last_name', 'like', '%' . $request->search . '%');
                });
            })->latest()->paginate(2);

        return view('dashboard.users.index', compact('users'));
    } // end of index

    
    public function create()
    {
        return view('dashboard.users.create');
    } // end of create

    
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'image' => 'image',
            'permissions' => 'required|min:1',
            'password' => 'required|confirmed',
        ]);    

        $request_data = $request->except(['password', 'password_confirmation', 'permissions', 'image']);
        $request_data['password'] = bcrypt($request->password);

        if($request->image) {
            Image::make($request->image)->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('uploads/user_images/' . $request->image->hashName()));

            $request_data['image'] = $request->image->hashName();
        } // end of image

        $user = User::create($request_data);
        $user->attachRole('admin');
        $user->syncPermissions($request->permissions);

        session()->flash('success', __('site.added_successfully'));
        
        return redirect()->route('dashboard.users.index');        
    } // end of store

   
    public function edit(User $user)
    {
        return view('dashboard.users.edit', compact('user'));
    }

    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => ['required', Rule::unique('users')->ignore($user->id),],
            'image' => 'image',
            'permissions' => 'required|min:1',
        ]);    

        $request_data = $request->except(['permissions', 'image']);

        if($request->image) {

            if($user->image != 'default.jpg') {

                Storage::disk('public_uploads')->delete('/user_images/' . $user->image);
            } // end of inner if
            
            $img_name = $request->image->hashName();
            Image::make($request->image)->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('uploads/user_images/' . $img_name));

            $request_data['image'] = $img_name;
        } // end of image

        $user->update($request_data);
        $user->syncPermissions($request->permissions);   
        
        session()->flash('success', __('site.updated_successfully'));

        return redirect()->route('dashboard.users.index');
    }

    
    public function destroy(User $user)
    {
        if($user->image != 'default.jpg') {
            Storage::disk('public_uploads')->delete('/user_images/' . $user->image);
        }

        $user->delete();
        session()->flash('success', __('site.deleted_successfully'));

        return redirect()->route('dashboard.users.index');
    }

} // end of controller

