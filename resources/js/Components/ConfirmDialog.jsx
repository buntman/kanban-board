import Modal from "@/Components/Modal";
import DangerButton from "@/Components/DangerButton";
import SecondaryButton from "@/Components/SecondaryButton";

export default function ConfirmDialog({
    show,
    title = "Are you sure?",
    message,
    confirmLabel = "Delete",
    processing = false,
    onConfirm,
    onClose,
}) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <div className="p-6">
                <h2 className="text-lg font-semibold text-gray-900">
                    {title}
                </h2>
                <p className="mt-2 text-sm text-gray-600">{message}</p>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={onClose} disabled={processing}>
                        Cancel
                    </SecondaryButton>
                    <DangerButton onClick={onConfirm} disabled={processing}>
                        {processing ? "Deleting…" : confirmLabel}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    );
}
