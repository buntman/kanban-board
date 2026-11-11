import { useState } from "react";
import { router } from "@inertiajs/react";
import TaskMenu from "./TaskMenu";

export default function TaskItem({ task, onDelete }) {
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
            <div className="flex items-start justify-between gap-2">
                <label className="flex flex-1 items-start gap-2 cursor-pointer min-w-0">
                    <input
                        type="checkbox"
                        checked={!!task.is_completed}
                        onChange={toggle}
                        disabled={processing}
                        className="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span
                        className={
                            (task.is_completed
                                ? "font-medium text-sm text-gray-400 line-through"
                                : "font-medium text-sm text-gray-800") +
                            " break-words min-w-0"
                        }
                    >
                        {task.name}
                    </span>
                </label>

                <div className="flex-shrink-0 -mt-1 -mr-1">
                    <TaskMenu onDelete={onDelete} />
                </div>
            </div>

            <p className="text-gray-400 text-xs mt-1 ml-6">
                {task.description || "No Description"}
            </p>
        </div>
    );
}
