import { ref } from "vue";

interface Toast {
    id: number;
    type: "success" | "error" | "info";
    message: string;
}

const toasts = ref<Toast[]>([]);
let nextId = 0;

export function useToast() {
    function show(
        message: string,
        type: Toast["type"] = "success",
        duration = 4000,
    ) {
        const id = ++nextId;
        toasts.value.push({ id, type, message });
        setTimeout(() => remove(id), duration);
    }

    function remove(id: number) {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }

    const success = (msg: string) => show(msg, "success");
    const error = (msg: string) => show(msg, "error");
    const info = (msg: string) => show(msg, "info");

    return { toasts, show, remove, success, error, info };
}
