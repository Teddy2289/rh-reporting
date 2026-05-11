import { ref } from "vue";
import { extractErrorMessage, extractValidationErrors } from "@/utils";
import { useToast } from "./useToast";

export function useApi() {
    const loading = ref(false);
    const error = ref<string | null>(null);
    const validationErrors = ref<Record<string, string[]>>({});
    const toast = useToast();

    async function execute<T>(
        fn: () => Promise<T>,
        options?: {
            successMessage?: string;
            errorMessage?: string;
            silent?: boolean;  // pas de toast du tout
        }
    ): Promise<T | null> {
        loading.value = true;
        error.value = null;
        validationErrors.value = {};

        try {
            const result = await fn();
            if (options?.successMessage) {
                toast.success(options.successMessage);
            }
            return result;
        } catch (err) {
            const msg =
                options?.errorMessage ??
                extractErrorMessage(err);
            error.value = msg;
            validationErrors.value = extractValidationErrors(err);
            if (!options?.silent) {
                toast.error(msg);
            }
            return null;
        } finally {
            loading.value = false;
        }
    }

    function clearErrors() {
        error.value = null;
        validationErrors.value = {};
    }

    return { loading, error, validationErrors, execute, clearErrors };
}