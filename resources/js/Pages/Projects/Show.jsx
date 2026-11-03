import React from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function Show({ project }) {
    // Form helper for adding a new Card (Column)
    const { data: cardData, setData: setCardData, post: postCard, processing: cardProcessing, reset: resetCard } = useForm({
        name: '',
        project_id: project.id,
    });

    const handleCreateCard = (e) => {
        e.preventDefault();
        // We will build this route next, but this sets up the frontend action
        post(route('project-cards.store'), {
            onSuccess: () => resetCard(),
        });
    };

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            {/* TOP HEADER */}
            <header className="bg-white border-b px-8 py-4 flex justify-between items-center shadow-sm">
                <div className="flex items-center space-x-4">
                    <Link href={route('projects.index')} className="text-gray-500 hover:text-gray-700 text-sm font-medium">
                        &larr; Back to Projects
                    </Link>
                    <h1 className="text-2xl font-bold text-gray-800">{project.name}</h1>
                </div>
                <div className="text-sm text-gray-500">
                    {project.description || 'Kanban Workspace'}
                </div>
            </header>

            {/* KANBAN BOARD CONTAINER (Horizontal Scroll) */}
            <main className="flex-1 p-8 overflow-x-auto flex items-start space-x-6">

                {/* MAP THROUGH PROJECT CARDS (COLUMNS) */}
                {project.project_cards && project.project_cards.map((card) => (
                    <div key={card.id} className="w-80 bg-gray-100 rounded-xl p-4 flex-shrink-0 flex flex-col max-h-[80vh] shadow-sm border">

                        {/* Column Header */}
                        <div className="flex justify-between items-center mb-4 px-1">
                            <h3 className="font-semibold text-gray-700">{card.name}</h3>
                            <span className="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full font-medium">
                                {card.tasks ? card.tasks.length : 0}
                            </span>
                        </div>

                        {/* Tasks List (Vertical Scroll if too many) */}
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1">
                            {card.tasks && card.tasks.map((task) => (
                                <div key={task.id} className="bg-white p-4 rounded-lg shadow-sm border hover:shadow transition">
                                    <h4 className="font-medium text-gray-800 text-sm">{task.name}</h4>
                                    <p className="text-gray-400 text-xs mt-1">{task.description || 'No Description'}</p>
                                </div>
                            ))}

                            {(!card.tasks || card.tasks.length === 0) && (
                                <div className="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg">
                                    <p className="text-xs text-gray-400">No tasks yet</p>
                                </div>
                            )}
                        </div>

                        {/* Add Task Button */}
                        <button className="mt-4 w-full py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-sm font-medium transition">
                            + Add a task
                        </button>
                    </div>
                ))}

                {/* ADD NEW COLUMN / CARD PLACEHOLDER */}
                <div className="w-80 bg-white/50 border-2 border-dashed border-gray-300 rounded-xl p-4 flex-shrink-0 flex flex-col justify-center items-center text-gray-500 hover:bg-white/80 transition">
                    <p className="text-sm font-medium mb-2">+ New Card (Column)</p>
                    {/* You can expand this into an inline form later! */}
                </div>

            </main>
        </div>
    );
}
