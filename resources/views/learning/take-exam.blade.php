<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $attempt->assessment->title ?? 'Exam' }} | Learning Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    <div class="max-w-4xl mx-auto py-8 px-4">
        
        @php
            $timeLimit = $attempt->assessment->time_limit; // in minutes
            $startedAt = $attempt->started_at;
            
            if ($timeLimit && $startedAt) {
                $endTime = $startedAt->copy()->addMinutes($timeLimit);
                $remainingSeconds = max(0, now()->diffInSeconds($endTime, false));
            } else {
                $remainingSeconds = null; // No limit
            }
        @endphp

        <!-- Header -->
        <div class="flex items-center justify-between mb-8 sticky top-0 bg-gray-50 z-10 py-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $attempt->assessment->title }}</h1>
                <p class="text-gray-500">{{ $attempt->training->title }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm font-medium text-gray-500">Time Remaining</div>
                <div id="timerDisplay" class="text-2xl font-bold text-indigo-600 font-mono">
                    {{ $timeLimit ? $timeLimit . ':00' : 'No Limit' }}
                </div>
            </div>
        </div>

        <form id="examForm" action="{{ route('exam.submit', $attempt->id) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                @foreach($attempt->assessment->questions as $index => $question)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $question->question_text }}</h3>
                            
                            @if($question->image_path)
                                <div class="mb-4 rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                                    <img src="{{ asset($question->image_path) }}" alt="Question Image" class="w-full h-auto max-h-96 object-contain mx-auto">
                                </div>
                            @endif

                            <div class="space-y-3">
                                @foreach($question->options as $option)
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" required>
                                    <span class="text-gray-700">{{ $option->option_text }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                    Submit Exam
                </button>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Timer Logic
            const remainingSeconds = {{ $remainingSeconds !== null ? $remainingSeconds : 'null' }};
            const timerDisplay = document.getElementById('timerDisplay');
            const examForm = document.getElementById('examForm');
            
            if (remainingSeconds !== null) {
                let timeLeft = remainingSeconds;
                
                function updateTimer() {
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        timerDisplay.innerText = "00:00:00";
                        timerDisplay.classList.add('text-red-600');
                        alert("Time's up! Submitting your exam now.");
                        examForm.submit();
                        return;
                    }

                    const hours = Math.floor(timeLeft / 3600);
                    const minutes = Math.floor((timeLeft % 3600) / 60);
                    const seconds = timeLeft % 60;

                    const formattedTime = 
                        (hours > 0 ? String(hours).padStart(2, '0') + ':' : '') +
                        String(minutes).padStart(2, '0') + ':' + 
                        String(seconds).padStart(2, '0');

                    timerDisplay.innerText = formattedTime;
                    
                    // Warning color when less than 5 minutes (300 seconds)
                    if (timeLeft <= 300) {
                        timerDisplay.classList.remove('text-indigo-600');
                        timerDisplay.classList.add('text-red-600', 'animate-pulse');
                    }

                    timeLeft--;
                }

                updateTimer(); // Initial call
                const timerInterval = setInterval(updateTimer, 1000);
                
                // Warn user before leaving page
                window.onbeforeunload = function() {
                    return "Are you sure you want to leave? Your exam timer will continue running.";
                };
                
                // Remove warning on submit
                examForm.addEventListener('submit', function() {
                    window.onbeforeunload = null;
                });
            }
        });
    </script>
</body>
</html>
