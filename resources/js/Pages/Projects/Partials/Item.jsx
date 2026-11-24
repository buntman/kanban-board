import { useState } from "react";
import { router } from "@inertiajs/react";
import TaskMenu from "./TaskMenu";

export default function TaskItem({
    task,
    onEdit,
    onDelete,
    isDragging = false,
    dropBefore = false,
    dropAfter = false,
    onDragStart,
    onDragEnd,
    onDragOver,
    onDrop,
}) {
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
        <div
            onDragOver={onDragOver}
            onDrop={onDrop}
            className={`bg-white p-4 rounded-lg shadow-sm border hover:shadow transition ${
                isDragging ? "opacity-40" : ""
            } ${dropBefore ? "border-t-2 border-indigo-500" : ""} ${
                dropAfter ? "border-b-2 border-indigo-500" : ""
            }`}
        >
            <div className="flex items-start justify-between gap-2">
                <span
                    draggable
                    onDragStart={onDragStart}
                    onDragEnd={onDragEnd}
                    title="Drag to move task"
                    aria-label="Drag to move task"
                    className="cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 px-1 select-none flex-shrink-0"
                >
                    <svg
                        className="w-4 h-4"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <circle cx="7" cy="5" r="1" />
                        <circle cx="13" cy="5" r="1" />
                        <circle cx="7" cy="10" r="1" />
                        <circle cx="13" cy="10" r="1" />
                        <circle cx="7" cy="15" r="1" />
                        <circle cx="13" cy="15" r="1" />
                    </svg>
                </span>

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
                    <TaskMenu onEdit={onEdit} onDelete={onDelete} />
                </div>
            </div>

            {task.description && (
                <p className="text-gray-500 text-xs mt-1 ml-6 whitespace-pre-line max-h-8 overflow-hidden">
                    {task.description}
                </p>
            )}
        </div>
    );
}
