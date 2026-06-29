<?php

namespace App\Mediators;

use App\Http\Resources\ResponseResource;
use App\Mediators\Contracts\WorkflowTemplateMediatorInterface;
use App\Repositories\Contracts\WorkflowTemplateRepositoryInterface;
use Illuminate\Support\Facades\DB;

class WorkflowTemplateMediator implements WorkflowTemplateMediatorInterface
{
    private $workflowTemplateRepository;

    public function __construct(WorkflowTemplateRepositoryInterface $workflowTemplateRepository)
    {
        $this->workflowTemplateRepository = $workflowTemplateRepository;
    }

    public function getAll()
    {
        return ResponseResource::make($this->workflowTemplateRepository->getAll());
    }

    public function findById($id)
    {
        return ResponseResource::make($this->workflowTemplateRepository->findById($id));
    }

    public function getByType($type)
    {
        return ResponseResource::make($this->workflowTemplateRepository->getByType($type));
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'type', 'company_id', 'is_default', 'is_active']);
            $template = $this->workflowTemplateRepository->store($data);

            // Create Step 0 (creation notification)
            if ($request->has('step0')) {
                $step0 = $request->input('step0');
                $template->steps()->create([
                    'step_order' => 0,
                    'name' => 'Creation',
                    'role_id' => null,
                    'notify_roles' => $step0['notify_roles'] ?? [],
                    'notify_department' => $step0['notify_department'] ?? true,
                    'description' => 'Notification on creation',
                ]);
            } else {
                $template->steps()->create([
                    'step_order' => 0,
                    'name' => 'Creation',
                    'role_id' => null,
                    'notify_roles' => [],
                    'notify_department' => true,
                    'description' => 'Notification on creation',
                ]);
            }

            // Create approval steps (step_order starts at 1)
            if ($request->has('steps')) {
                foreach ($request->input('steps') as $index => $step) {
                    $template->steps()->create([
                        'step_order' => $index + 1,
                        'name' => $step['name'],
                        'role_id' => $step['role_id'],
                        'notify_roles' => $step['notify_roles'] ?? null,
                        'description' => $step['description'] ?? null,
                    ]);
                }
            }

            // If this is set as default, unset other defaults of the same type
            if ($template->is_default) {
                \App\Models\WorkflowTemplate::where('type', $template->type)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }

            DB::commit();
            return ResponseResource::make(
                $this->workflowTemplateRepository->findById($template->id)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $template = $this->workflowTemplateRepository->find($id);
            if (!$template) {
                return ResponseResource::make(['success' => false]);
            }

            $template->update($request->only(['name', 'type', 'company_id', 'is_default', 'is_active']));

            // Update Step 0 (creation notification)
            if ($request->has('step0')) {
                $step0Data = $request->input('step0');
                $step0 = $template->steps()->where('step_order', 0)->first();
                if ($step0) {
                    $step0->update([
                        'notify_roles' => $step0Data['notify_roles'] ?? [],
                        'notify_department' => $step0Data['notify_department'] ?? true,
                    ]);
                } else {
                    $template->steps()->create([
                        'step_order' => 0,
                        'name' => 'Creation',
                        'role_id' => null,
                        'notify_roles' => $step0Data['notify_roles'] ?? [],
                        'notify_department' => $step0Data['notify_department'] ?? true,
                        'description' => 'Notification on creation',
                    ]);
                }
            }

            if ($request->has('steps')) {
                // Delete existing approval steps only (keep step 0)
                $template->steps()->where('step_order', '>', 0)->delete();

                foreach ($request->input('steps') as $index => $step) {
                    $template->steps()->create([
                        'step_order' => $index + 1,
                        'name' => $step['name'],
                        'role_id' => $step['role_id'],
                        'notify_roles' => $step['notify_roles'] ?? null,
                        'description' => $step['description'] ?? null,
                    ]);
                }
            }

            // If this is set as default, unset other defaults of the same type
            if ($template->is_default) {
                \App\Models\WorkflowTemplate::where('type', $template->type)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }

            DB::commit();
            return ResponseResource::make(
                $this->workflowTemplateRepository->findById($template->id)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $result = $this->workflowTemplateRepository->destroy($id);
        if ($result === 0) {
            return ResponseResource::make(['success' => false]);
        }
        return ResponseResource::make(['success' => true]);
    }
}
