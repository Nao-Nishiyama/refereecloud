<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AnnualReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AnnualReportRequest;

class AnnualReportController extends Controller
{
    private $annual_report;
    private $user;

    public function __construct(AnnualReport $annual_report, User $user)
    {
        $this->annual_report = $annual_report;
        $this->user = $user;
    }

    public function show($id)
    {
        $user = $this->user->findOrFail($id);
        $reports = $this->annual_report->where('user_id', $user->id)->orderBy('year', 'desc')->get();
        
        return view('users.reports.show', compact('reports', 'user'));
    }


    public function create()
    {
        return view('users.reports.create');    
    }

    public function store(AnnualReportRequest $request)
    {
        # save the report
        $annualReport = new AnnualReport();
        $annualReport->user_id =  Auth::user()->id;
        $annualReport->year = $request->year;
        $annualReport->first_ref_block = $request->first_ref_block;
        $annualReport->second_ref_block = $request->second_ref_block;
        $annualReport->first_ref = $request->first_ref;
        $annualReport->second_ref = $request->second_ref;
        $annualReport->scorer = $request->scorer;
        $annualReport->assistant_scorer = $request->assistant_scorer;
        $annualReport->linejudge = $request->linejudge;
        $annualReport->training = $request->training;
        $annualReport->save();

        return redirect()->route('reports.show', Auth::user()->id)
                ->with('user', Auth::user()->id)
                ->with('success', '活動報告を登録しました');
    }

}
