<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info('Attempting to retrieve tasks');

        $tasks = Task::query();

        // Example: filter by 'status' if provided in query string
        if ($request->has('status')) {
            Log::info('Filtering by status', ['status' => $request->input('status')]);
            $tasks->where(function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            });
        }

        // Example: filter by 'user_id' if provided in query string
        if ($request->has('user_id')) {
            Log::info('Filtering by user_id', ['user_id' => $request->input('user_id')]);
            $tasks->where(function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            });
        }

        $tasks = $tasks->get();

        Log::info('Tasks retrieved successfully');

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => TaskResource::collection($tasks),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        try {
            Log::info('Attempting to create a new task');

            $task = Task::create($request->validated());

            Log::info('Task created successfully', ['task_id' => $task->id]);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => new TaskResource($task),
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create task', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        try {
            Log::info('Attempting to retrieve task', ['task_id' => $task->id]);

            $tasks = TaskResource::make($task);

            Log::info('Task retrieved successfully', ['task_id' => $task->id]);

            return response()->json([
                'success' => true,
                'message' => 'Task '.$task->id.' retrieved successfully',
                'data' => $tasks,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve task', ['task_id' => $task->id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        try {
            $task->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => new TaskResource($task),
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to update task', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        try {
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to delete task', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
