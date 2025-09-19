<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(20);
        return view('dashboard.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('dashboard.categories.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7', // hex code
        ]);

        Category::create($validated);

        return redirect()->route('dashboard.categories.index')->with('success', 'Category created!');
    }

    public function edit(Category $category)
    {
        return view('dashboard.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7', // hex code
        ]);

        $category->update($validated);

        return redirect()->route('dashboard.categories.index')->with('success', 'Category updated!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted!');
    }
}
