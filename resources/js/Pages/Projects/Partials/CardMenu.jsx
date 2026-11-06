import { useState } from "react";
import { useForm } from "@inertiajs/react";
import Dropdown from "@/Components/Dropdown";
import Modal from "@/Components/Modal";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";

export default function CardMenu({ card }) {
    const [showRenameModal, setShowRenameModal] = useState(false);

    const { data, setData, patch, processing, errors, reset, clearErrors } =
        useForm({ name: card.name });

    const openRenameModal = () => {
        setData("name", card.name);
        setShowRenameModal(true);
    };

    const closeRenameModal = () => {
        setShowRenameModal(false);
        clearErrors();
        reset();
    };

    const submitRename = (e) => {
        e.preventDefault();

        patch(route("project-cards.update", card.id), {
            preserveScroll: true,
            onSuccess: () => closeRenameModal(),
        });
    };

    return (
        <>
            <Dropdown>
                <Dropdown.Trigger>
                    <button
                        type="button"
                        aria-label="Card options"
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
                                d="M12 5v.01M12 12v.01M12 19v.01"
                            />
                        </svg>
                    </button>
                </Dropdown.Trigger>

                <Dropdown.Content width="48">
                    <button
                        type="button"
                        onClick={openRenameModal}
                        className="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100 transition"
                    >
                        Rename card
                    </button>
                </Dropdown.Content>
            </Dropdown>

            <Modal
                show={showRenameModal}
                onClose={closeRenameModal}
                maxWidth="sm"
            >
                <form onSubmit={submitRename} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Rename card
                    </h2>

                    <input
                        type="text"
                        value={data.name}
                        onChange={(e) => setData("name", e.target.value)}
                        autoFocus
                        className="mt-4 w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />

                    <InputError message={errors.name} className="mt-2" />

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeRenameModal}>
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton className="ms-3" disabled={processing}>
                            Save
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>
        </>
    );
}
