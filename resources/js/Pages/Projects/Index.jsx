import React from 'react';
import { useForm } from '@inertiajs/react';

export default function Index({ projects }) {
    // Inertia's form helper
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('projects.store'), {
            onSuccess: () => reset(), // Clears the form inputs after successful creation
        });
    };

    return (
        <div className="p-8 max-w-7xl mx-auto">
            <h1 className="text-3xl font-bold text-gray-800 mb-8">Projects</h1>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {/* LEFT COLUMN: Create Form */}
                <div className="bg-white p-6 rounded-lg shadow border h-fit">
                    <h2 className="text-xl font-semibold text-gray-700 mb-4">Create New Project</h2>

                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Project Name</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                                placeholder="e.g. Website Redesign"
                            />
                            {errors.name && <div className="text-red-500 text-sm mt-1">{errors.name}</div>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                                rows="3"
                                placeholder="Optional details..."
                            />
                            {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50 font-medium"
                        >
                            Create Project
                        </button>
                    </form>
                </div>

                {/* RIGHT 2 COLUMNS: Projects List */}
                <div className="lg:col-span-2">
                    <h2 className="text-xl font-semibold text-gray-700 mb-4">Existing Projects</h2>

                    {projects.length === 0 ? (
                        <div className="bg-white p-6 rounded-lg shadow border text-gray-500 text-center">
                            No projects found. Use the form on the left to create your first one!
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {projects.map((project) => (
                                <div key={project.id} className="bg-white p-6 rounded-lg shadow border hover:shadow-md transition">
                                    <h3 className="text-lg font-semibold text-gray-800">{project.name}</h3>
                                    <p className="text-gray-500 text-sm mt-1">{project.description || 'No description provided.'}</p>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

            </div>
        </div>
    );
}
