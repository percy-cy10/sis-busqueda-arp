import { api } from "src/boot/axios";

class PagoService {
    static async getData(params) {
        // return (await api.get('/api/pagos',params)).data;
        return (await api.get('/api/pagos', { params })).data;

    }

    static async get(id) {
        return (await api.get(`/api/pagos/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/pagos/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/pagos", reg)).data;
        } else {
            return (await api.put(`/api/pagos/${reg.id}`, reg)).data;
        }
    }

    // static async toggleEstado(id) {
    //     try {
    //         return (await api.patch(`/api/pagos/${id}/toggle-estado`)).data;
    //     } catch (error) {
    //         console.error("Error al cambiar el estado del pago:", error);
    //         throw error;
    //     }
    // }
    static async anular(id) {
        return (await api.put(`/api/pagos/${id}/anular`)).data;
    }

    // En PagoService.js
    static async toggleEstado(id, data = {}) {
        try {
            return (await api.put(`/api/pagos/${id}/toggle-estado`, data)).data;
        } catch (error) {
            console.error("Error al cambiar el estado del pago:", error);
            throw error;
        }
    }





}

export default PagoService;
