<?php

namespace App\Http\Controllers;

use App\Models\DonorMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

/** "Requests for you": the signed-in donor's matches and their accept / decline answers. */
class DonorMatchController extends Controller
{
    public function index(Request $request): View
    {
        $donor = $request->user()->donor()->firstOrFail();

        return view('matches.index', [
            'matches' => $donor->matches()
                ->with(['bloodRequest.hospital', 'bloodRequest.requester'])
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function update(Request $request, int $donorMatch): RedirectResponse
    {
        // Ownership first, so other donors' match ids give 404 whatever the input.
        $match = $request->user()->donor()->firstOrFail()->matches()->findOrFail($donorMatch);

        $answer = $request->validate([
            'answer' => ['required', Rule::in(DonorMatch::ANSWERS)],
        ])['answer'];

        try {
            $match->respond($answer);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('matches.index')->with('status', $answer === 'accepted'
            ? 'Thank you! The requester can now see your name and mobile number.'
            : 'You declined this request.');
    }

    /** An accepted donor cannot make it after all (DEC-022). */
    public function withdraw(Request $request, int $donorMatch): RedirectResponse
    {
        $match = $request->user()->donor()->firstOrFail()->matches()->findOrFail($donorMatch);

        try {
            $match->withdraw();
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('matches.index')
            ->with('status', 'You withdrew. The requester was told, so they can look for another donor.');
    }
}
