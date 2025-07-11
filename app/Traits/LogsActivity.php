<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Log a custom activity
     */
    protected function logActivity(string $action, string $description, ?Model $subject = null, array $properties = []): ActivityLog
    {
        $data = [
            'action' => $action,
            'description' => $description,
            'properties' => $properties
        ];

        if ($subject) {
            $data['subject_type'] = get_class($subject);
            $data['subject_id'] = $subject->id;
            $data['subject_name'] = method_exists($subject, 'getLogName') 
                ? $subject->getLogName() 
                : ($subject->name ?? $subject->full_name ?? "ID: {$subject->id}");
            $data['module'] = $this->getModuleFromModel($subject);
        }

        return ActivityLog::logActivity($data);
    }

    /**
     * Log model creation
     */
    protected function logModelCreated(Model $model, ?string $customDescription = null): ActivityLog
    {
        $modelName = class_basename($model);
        $subjectName = method_exists($model, 'getLogName') 
            ? $model->getLogName() 
            : ($model->name ?? $model->full_name ?? "ID: {$model->id}");

        $description = $customDescription ?? auth()->user()->name . " created {$modelName}: {$subjectName}";

        return ActivityLog::logModelActivity('create', $model, null, $model->getAttributes(), $description);
    }

    /**
     * Log model update
     */
    protected function logModelUpdated(Model $model, array $oldValues, ?string $customDescription = null): ActivityLog
    {
        $modelName = class_basename($model);
        $subjectName = method_exists($model, 'getLogName') 
            ? $model->getLogName() 
            : ($model->name ?? $model->full_name ?? "ID: {$model->id}");

        $description = $customDescription ?? auth()->user()->name . " updated {$modelName}: {$subjectName}";

        return ActivityLog::logModelActivity('update', $model, $oldValues, $model->getAttributes(), $description);
    }

    /**
     * Log model deletion
     */
    protected function logModelDeleted(Model $model, ?string $customDescription = null): ActivityLog
    {
        $modelName = class_basename($model);
        $subjectName = method_exists($model, 'getLogName') 
            ? $model->getLogName() 
            : ($model->name ?? $model->full_name ?? "ID: {$model->id}");

        $description = $customDescription ?? auth()->user()->name . " deleted {$modelName}: {$subjectName}";

        return ActivityLog::logModelActivity('delete', $model, $model->getAttributes(), null, $description);
    }

    /**
     * Log bulk operations
     */
    protected function logBulkOperation(string $action, string $modelType, int $count, array $properties = []): ActivityLog
    {
        $user = auth()->user();
        $description = "{$user->name} performed bulk {$action} on {$count} {$modelType} records";

        return $this->logActivity("bulk_{$action}", $description, null, array_merge([
            'affected_count' => $count,
            'model_type' => $modelType
        ], $properties));
    }

    /**
     * Log verification actions
     */
    protected function logVerification(Model $model, bool $verified = true): ActivityLog
    {
        $modelName = class_basename($model);
        $subjectName = method_exists($model, 'getLogName') 
            ? $model->getLogName() 
            : ($model->name ?? $model->full_name ?? "ID: {$model->id}");

        $action = $verified ? 'verified' : 'rejected';
        $description = auth()->user()->name . " {$action} {$modelName}: {$subjectName}";

        return ActivityLog::logModelActivity($action, $model, null, null, $description);
    }

    /**
     * Log export operations
     */
    protected function logExport(string $type, int $recordCount, string $format = 'unknown'): ActivityLog
    {
        $user = auth()->user();
        $description = "{$user->name} exported {$recordCount} {$type} records as {$format}";

        return $this->logActivity('export', $description, null, [
            'export_type' => $type,
            'record_count' => $recordCount,
            'format' => $format
        ]);
    }

    /**
     * Log import operations
     */
    protected function logImport(string $type, int $recordCount, string $filename = 'unknown'): ActivityLog
    {
        $user = auth()->user();
        $description = "{$user->name} imported {$recordCount} {$type} records from {$filename}";

        return $this->logActivity('import', $description, null, [
            'import_type' => $type,
            'record_count' => $recordCount,
            'filename' => $filename
        ]);
    }

    /**
     * Log bill generation
     */
    protected function logBillGeneration(int $billCount, string $period): ActivityLog
    {
        $user = auth()->user();
        $description = "{$user->name} generated {$billCount} bills for period: {$period}";

        return $this->logActivity('bill_generation', $description, null, [
            'bill_count' => $billCount,
            'billing_period' => $period
        ]);
    }

    /**
     * Log payment processing
     */
    protected function logPayment(Model $bill, float $amount, string $method): ActivityLog
    {
        $user = auth()->user();
        $billNumber = $bill->bill_number ?? "ID: {$bill->id}";
        $description = "{$user->name} processed payment of {$amount} for bill {$billNumber} via {$method}";

        return ActivityLog::logModelActivity('payment', $bill, null, [
            'amount' => $amount,
            'payment_method' => $method
        ], $description);
    }

    /**
     * Log report generation
     */
    protected function logReportGeneration(string $reportType, array $filters = []): ActivityLog
    {
        $user = auth()->user();
        $description = "{$user->name} generated {$reportType} report";

        return $this->logActivity('report_generation', $description, null, [
            'report_type' => $reportType,
            'filters' => $filters
        ]);
    }

    /**
     * Log system configuration changes
     */
    protected function logConfigurationChange(string $setting, $oldValue, $newValue): ActivityLog
    {
        $user = auth()->user();
        $description = "{$user->name} changed system setting '{$setting}' from '{$oldValue}' to '{$newValue}'";

        return $this->logActivity('configuration_change', $description, null, [
            'setting' => $setting,
            'old_value' => $oldValue,
            'new_value' => $newValue
        ]);
    }

    /**
     * Get module name from model
     */
    private function getModuleFromModel(Model $model): string
    {
        $modelName = class_basename($model);
        
        return match($modelName) {
            'Customer' => 'customers',
            'WaterMeter' => 'meters',
            'MeterReading' => 'readings',
            'Bill' => 'bills',
            'Rate' => 'rates',
            'User' => 'users',
            default => strtolower($modelName)
        };
    }
} 