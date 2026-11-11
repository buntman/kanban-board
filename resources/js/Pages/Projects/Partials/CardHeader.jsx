import { useState } from "react";
import { useForm } from "@inertiajs/react";
import CardMenu from "./CardMenu";
import InputError from "@/Components/InputError";

export default function CardHeader({ card, onDelete }) {
    const [isEditing, setIsEditing] = useState(false);

    const { data, setData, patch, processing, errors, reset, clearErrors } =
        useForm({ name: card.name });

    const startEditing = () => {
        setData("name", card.name);
        setIsEditing(true);
    };

    const cancelEditing = () => {
        setIsEditing(false);
        clearErrors();
        reset();
    };

    const submit = (e) => {
        e.preventDefault();

        patch(route("project-cards.update", card.id), {
            preserveScroll: true,
            onSuccess: () => setIsEditing(false),
        });
    };

    return (
        <div className="flex justify-between items-start mb-4 px-1 gap-2">
            {isEditing ? (
                <div className="flex-1">
                    <form onSubmit={submit} className="flex items-center gap-1">
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            onKeyDown={(e) =>
                                e.key === "Escape" && cancelEditing()
                            }
                            autoFocus
                            className="flex-1 min-w-0 text-sm font-semibold rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-1.5"
                        />
                        <button
                            type="submit"
                            disabled={processing}
                            aria-label="Save name"
                            className="text-green-600 hover:text-green-700 p-1 disabled:opacity-50"
                        >
                            <svg
                                className="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </button>
                        <button
                            type="button"
                            onClick={cancelEditing}
                            aria-label="Cancel"
                            className="text-gray-400 hover:text-gray-600 p-1"
                        >
                            <svg
                                className="w-4 h-4"
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
                    </form>
                    <InputError message={errors.name} className="mt-1" />
                </div>
            ) : (
                <>
                    <h3 className="font-semibold text-gray-700 flex-1">
                        {card.name}
                    </h3>
                    <div className="flex items-center space-x-2 flex-shrink-0">
                        <CardMenu
                            onRename={startEditing}
                            onDelete={onDelete}
                        />
                    </div>
                </>
            )}
        </div>
    );
}
