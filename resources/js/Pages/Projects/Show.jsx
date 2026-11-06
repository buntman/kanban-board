import React, { useState } from "react";
import { Link, useForm } from "@inertiajs/react";
import TaskForm from "./Partials/TaskForm";
import TaskItem from "./Partials/Item";
import CardHeader from "./Partials/CardHeader";

export default function Show({ project }) {
    const [isCreatingCard, setIsCreatingCard] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
    });

    const handleCreateCard = (e) => {
        e.preventDefault();
        // We will build this route next, but this sets up the frontend action
        post(route("project-cards.store", project.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset(); // Clears the input field
                setIsCreatingCard(false); // Closes the form popup
            },
        });
    };

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            {/* TOP HEADER */}
            <header className="bg-white border-b px-8 py-4 flex justify-between items-center shadow-sm">
                <div className="flex items-center space-x-4">
                    <Link
                        href={route("projects.index")}
                        className="text-gray-500 hover:text-gray-700 text-sm font-medium"
                    >
                        &larr; Back to Projects
                    </Link>
                    <h1 className="text-2xl font-bold text-gray-800">
                        {project.name}
                    </h1>
                </div>
                <div className="text-sm text-gray-500">
                    {project.description || "Kanban Workspace"}
                </div>
            </header>

            {/* KANBAN BOARD CONTAINER (Horizontal Scroll) */}
            <main className="flex-1 p-8 overflow-x-auto flex items-start space-x-6">
                {/* MAP THROUGH PROJECT CARDS (COLUMNS) */}
                {project.project_cards &&
                    project.project_cards.map((card) => (
                        <div
                            key={card.id}
                            className="w-80 bg-gray-100 rounded-xl p-4 flex-shrink-0 flex flex-col max-h-[80vh] shadow-sm border"
                        >
                            {/* Column Header */}
                            <CardHeader card={card} />

                            {/* Tasks List (Vertical Scroll if too many) */}
                            <div className="flex-1 overflow-y-auto space-y-3 pr-1">
                                {card.tasks &&
                                    card.tasks.map((task) => (
                                        <TaskItem key={task.id} task={task} />
                                    ))}
                                {(!card.tasks || card.tasks.length === 0) && (
                                    <div className="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg">
                                        <p className="text-xs text-gray-400">
                                            No tasks yet
                                        </p>
                                    </div>
                                )}
                            </div>

                            {/* Add Task */}
                            <TaskForm card={card} />
                        </div>
                    ))}

                <div className="w-80 flex-shrink-0">
                    {!isCreatingCard ? (
                        /* STATE A: Just the clean button */
                        <button
                            onClick={() => setIsCreatingCard(true)}
                            className="w-full bg-white/60 hover:bg-white border-2 border-dashed border-gray-300 hover:border-gray-400 rounded-xl p-4 text-gray-600 font-medium text-sm flex items-center justify-center transition shadow-sm"
                        >
                            + New Card (Column)
                        </button>
                    ) : (
                        /* STATE B: The popup/slide-down form */
                        <div className="bg-white rounded-xl p-4 shadow-md border border-gray-200">
                            <form
                                onSubmit={handleCreateCard}
                                className="space-y-3"
                            >
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData("name", e.target.value)
                                    }
                                    placeholder="Enter card name..."
                                    autoFocus // Automatically focuses the input when opened
                                    className="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                                />
                                {errors.name && (
                                    <div className="text-red-500 text-xs">
                                        {errors.name}
                                    </div>
                                )}

                                <div className="flex items-center space-x-2">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-2 rounded-md transition disabled:opacity-50"
                                    >
                                        Add Card
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            reset();
                                            setIsCreatingCard(false);
                                        }}
                                        className="text-gray-400 hover:text-gray-600 p-1 rounded transition"
                                    >
                                        {/* X Close Icon */}
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
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}
