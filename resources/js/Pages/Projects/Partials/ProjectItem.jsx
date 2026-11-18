import { useState } from "react";
import { Link, router, useForm } from "@inertiajs/react";
import Dropdown from "@/Components/Dropdown";
import ConfirmDialog from "@/Components/ConfirmDialog";
import InputError from "@/Components/InputError";

export default function ProjectItem({ project }) {
    const [isEditing, setIsEditing] = useState(false);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    const {
        data,
        setData,
        patch,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        name: project.name,
        description: project.description ?? "",
    });

    const startEditing = () => {
        setData({
            name: project.name,
            description: project.description ?? "",
        });
        clearErrors();
        setIsEditing(true);
    };

    const cancelEditing = () => {
        clearErrors();
        reset();
        setIsEditing(false);
    };

    const submit = (e) => {
        e.preventDefault();

        patch(route("projects.update", project.id), {
            preserveScroll: true,
            onSuccess: () => setIsEditing(false),
        });
    };

    const deleteProject = () => {
        router.delete(route("projects.destroy", project.id), {
            preserveScroll: true,
            onStart: () => setIsDeleting(true),
            onFinish: () => setIsDeleting(false),
            onSuccess: () => setShowDeleteConfirm(false),
        });
    };

    return (
        <div className="bg-white p-6 rounded-lg shadow border hover:shadow-md transition">
            {isEditing ? (
                <form onSubmit={submit} className="space-y-3">
                    <div>
                        <label
                            htmlFor={`project-name-${project.id}`}
                            className="block text-sm font-medium text-gray-700"
                        >
                            Project Name
                        </label>
                        <input
                            id={`project-name-${project.id}`}
                            type="text"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            autoFocus
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                        />
                        <InputError message={errors.name} className="mt-1" />
                    </div>

                    <div>
                        <label
                            htmlFor={`project-description-${project.id}`}
                            className="block text-sm font-medium text-gray-700"
                        >
                            Description
                        </label>
                        <textarea
                            id={`project-description-${project.id}`}
                            value={data.description}
                            onChange={(e) =>
                                setData("description", e.target.value)
                            }
                            rows="3"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                        />
                        <InputError
                            message={errors.description}
                            className="mt-1"
                        />
                    </div>

                    <div className="flex items-center space-x-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-indigo-600 text-white text-sm font-medium px-3 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Save
                        </button>
                        <button
                            type="button"
                            onClick={cancelEditing}
                            disabled={processing}
                            className="text-gray-500 hover:text-gray-700 text-sm font-medium px-3 py-2 rounded-md disabled:opacity-50"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            ) : (
                <div className="flex items-start justify-between gap-2">
                    <Link
                        href={route("projects.show", project.id)}
                        className="flex-1 min-w-0"
                    >
                        <h3 className="text-lg font-semibold text-gray-800">
                            {project.name}
                        </h3>
                        <p className="text-gray-500 text-sm mt-1">
                            {project.description || "No description provided."}
                        </p>
                    </Link>

                    <Dropdown>
                        <Dropdown.Trigger>
                            <button
                                type="button"
                                aria-label={`Options for ${project.name}`}
                                className="text-gray-400 hover:text-gray-600 p-1 rounded transition flex-shrink-0"
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
                                        d="M5 12h.01M12 12h.01M19 12h.01"
                                    />
                                </svg>
                            </button>
                        </Dropdown.Trigger>

                        <Dropdown.Content width="48">
                            <Dropdown.Item
                                onClick={startEditing}
                                className="text-gray-700"
                            >
                                Rename project
                            </Dropdown.Item>
                            <Dropdown.Item
                                onClick={() => setShowDeleteConfirm(true)}
                                className="text-red-600 hover:bg-red-50 data-[focus]:!bg-red-50"
                            >
                                Delete project
                            </Dropdown.Item>
                        </Dropdown.Content>
                    </Dropdown>
                </div>
            )}

            <ConfirmDialog
                show={showDeleteConfirm}
                title="Delete project?"
                message={`"${project.name}" and all of its columns and tasks will be permanently deleted.`}
                confirmLabel="Delete project"
                processing={isDeleting}
                onConfirm={deleteProject}
                onClose={() => setShowDeleteConfirm(false)}
            />
        </div>
    );
}
