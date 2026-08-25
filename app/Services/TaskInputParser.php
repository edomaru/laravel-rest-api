<?php
namespace App\Services;
 
use App\Models\Priority;
use Carbon\Carbon;
 
class TaskInputParser
{
    public function parse(string $input): ?array
    {
        $priorityId = null;
        if (preg_match('/!(low|medium|high)/i', $input, $matches)) {
            $priority = strtolower($matches[1]);
            $priorityId = Priority::whereName($priority)->value('id') ?? null;
        }

        $dueDate = null;
        if (preg_match('/@(.+?)(?:\s+!|\s*$)/', $input, $matches)) {
            $raw = strtolower(trim($matches[1]));
            $dueDate = match($raw) {
                'today' => now(),
                'tomorrow' => now()->addDay(),
                'next2d' => now()->addDays(2),
                'next3d' => now()->addDays(3),
                'nextweek' => now()->addWeek(),
                default => Carbon::parse($raw, now()->getTimezone())
            };
        }

        $patterns = [
            '/\s?@(.+?)(?:\s+!|\s*$)/', // Remove due date (and the space in front of it)
            '/\s?!?(low|medium|high)/i' // Remove priority (and the space in front of it)
        ];
        
        $name = preg_replace($patterns, '', $input);
        $name = trim($name);

        if (empty($name)) {
            return null;
        }

        return [
            'priority_id' => $priorityId,
            'due_date' => $dueDate,
            'name' => $name
        ];
    }
}