import { ref } from "vue";
import { extractErrorMessage, extractValidationErrors } from "@/utils";

/**
 * Composable générique pour wrapper les appels API avec gestion
 * automatique du loading, des erreurs et des erreurs de validation.
 *
 * @example
 * const { execute, loading, error } = useApi()
 * await execute(() => agentsApi.index())
 */
export function useApi() {
    const loading = ref(false);
    const error = ref<string | null>(null);
    const validationErrors = ref<Record<string, string[]>>({});

    async function execute<T>(fn: () => Promise<T>): Promise<T | null> {
        loading.value = true;
        error.value = null;
        validationErrors.value = {};

        try {
            return await fn();
        } catch (err) {
            error.value = extractErrorMessage(err);
            validationErrors.value = extractValidationErrors(err);
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
