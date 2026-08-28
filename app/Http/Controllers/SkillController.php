<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SkillController extends Controller
{
    public static function index()
    {
        $skillsCategoryList = DB::connection('zen')->table('tbl_skill_category')
        ->where('sc_stat', '=', '1')
        ->orderByRaw("IF(sc_id = 7, 1, 0) asc")
        ->orderBy('sc_title', 'asc')
        ->get();

        $skillsList = DB::connection('zen')->table('tbl_skill_type as a')
        ->where('a.status', '=', '1')
        ->orderBy('skill_name', 'asc')
        ->get();

        $skill = auth()->user()->skill
        ->map(function($s) use($skillsCategoryList, $skillsList){
            $s->sc_title = $skillsCategoryList->where('sc_id', $s->skill_category)->first()?->sc_title;
            $s->skill_name = $skillsList->where('id', $s->skill_type)->first()?->skill_name;
            return $s;
        });

        return view('pages.skill', compact('skill', 'skillsCategoryList', 'skillsList'));
    }

    public static function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'skill-id' => 'nullable|numeric',
                'skill-category' => 'required|numeric',
                'skill-type' => 'nullable|numeric',
                'skill-other' => 'nullable|string'
            ]);

            $validator->setAttributeNames([
                'skill-category' => 'Category',
                'skill-type' => 'Type',
                'skill-other' => 'Other',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            $skill = auth()->user()->skill()->firstOrNew(['skill_id' => $validated['skill-id']]);

            $skill->skill_category = $validated['skill-category'];
            $skill->skill_type = $validated['skill-type'];
            $skill->skill_others = $validated['skill-other'];
            $skill->status = 1;

            $skill->save();

            return redirect()->route('skill.index')->with('success', 'Skill info updated');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            auth()->user()->skill()->find($id)->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }
}
