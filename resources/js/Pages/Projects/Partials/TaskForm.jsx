import { useState } from "react";
import { useForm } from "@inertiajs/react";

export default function TaskForm({ card }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
    });

    const close = () => {
        reset();
        setIsOpen(false);
    };

    const submit = (e) => {
        e.preventDefault();

        post(route("tasks.store", card.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setIsOpen(false);
            },
        });
    };

    if (!isOpen) {
        return (
            <button
                type="button"
                onClick={() => setIsOpen(true)}
                className="mt-4 w-full py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-sm font-medium transition"
            >
                + Add a task
            </button>
        );
    }

    return (
        <form onSubmit={submit} className="mt-4 space-y-2">
            <input
                type="text"
                value={data.name}
                onChange={(e) => setData("name", e.target.value)}
                placeholder="Enter task name..."
                aria-label="Task name"
                autoFocus
                className="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
            />

            {errors.name && (
                <div className="text-red-500 text-xs">{errors.name}</div>
            )}

            <div className="flex items-center space-x-2">
                <button
                    type="submit"
                    disabled={processing}
                    className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-2 rounded-md transition disabled:opacity-50"
                >
                    Add Task
                </button>
                <button
                    type="button"
                    onClick={close}
                    aria-label="Cancel"
                    className="text-gray-400 hover:text-gray-600 p-1 rounded transition"
                >
                    <svg
                        className="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </form>
    );
}
