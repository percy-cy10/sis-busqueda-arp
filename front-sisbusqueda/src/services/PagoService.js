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

    static async toggleEstado(id) {
        try {
            return (await api.patch(`/api/pagos/${id}/toggle-estado`)).data;
        } catch (error) {
            console.error("Error al cambiar el estado del pago:", error);
            throw error;
        }
    }
    static async anular(id) {
        return (await api.put(`/api/pagos/${id}/anular`)).data;
    }



  //   static async toggleEstado(id) {
  //   try {
  //     // Llama a /api/pagos/{id}/toggle-estado
  //     const response = await api.patch(`/pagos/${id}/toggle-estado`);
  //     return response.data;
  //   } catch (error) {
  //     console.error('Error al cambiar el estado del pago:', error);
  //     if (error.response) {
  //       console.error('Respuesta del servidor:', error.response.data);
  //     }
  //     throw new Error(
  //       error.response?.data?.message || 'Error al cambiar el estado del pago'
  //     );
  //   }
  // }

}

export default PagoService;
