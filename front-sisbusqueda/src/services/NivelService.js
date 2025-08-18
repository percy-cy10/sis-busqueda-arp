import { api } from "src/boot/axios";

class NivelService {
  static async getData(params) {
    try {
      const response = await api.get("/api/niveles", params);
      return response.data;
    } catch (error) {
      console.error("Error al obtener los datos de niveles:", error);
      throw error;
    }
  }

  static async get(id) {
    return (await api.get(`/api/niveles/${id}`)).data;
  }

  static async delete(id) {
    return await api.delete(`/api/niveles/${id}`);
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("/api/niveles", reg)).data;
    } else {
      return (await api.put(`/api/niveles/${reg.id}`, reg)).data;
    }
  }
}

export default NivelService;
