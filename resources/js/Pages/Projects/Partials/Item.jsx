import { useState } from "react";
import { router } from "@inertiajs/react";

export default function TaskItem({ task }) {
    const [processing, setProcessing] = useState(false);

    const toggle = () => {
        router.patch(
            route("tasks.update", task.id),
            { is_completed: !task.is_completed },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
            }
        );
    };

    return (
        <div className="bg-white p-4 rounded-lg shadow-sm border hover:shadow transition">
            <label className="flex items-start gap-2 cursor-pointer">
                <input
                    type="checkbox"
                    checked={!!task.is_completed}
                    onChange={toggle}
                    disabled={processing}
                    className="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span
                    className={
                        task.is_completed
                            ? "font-medium text-sm text-gray-400 line-through"
                            : "font-medium text-sm text-gray-800"
                    }
                >
                    {task.name}
                </span>
            </label>
            <p className="text-gray-400 text-xs mt-1 ml-6">
                {task.description || "No Description"}
            </p>
        </div>
    );
}
