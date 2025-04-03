import { api } from "src/boot/axios";

class FavorecidoService {
  static async getData(params) {
    return (await api.get("/api/favorecidos", params)).data;
  }

  static async get(id) {
    return (await api.get(`/api/favorecidos/${id}`)).data;
  }

  static async delete(id) {
    return await api.delete(`/api/favorecidos/${id}`);
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (await api.post("/api/favorecidos", reg)).data;
    } else {
      return (await api.put(`/api/favorecidos/${reg.id}`, reg)).data;
    }
  }
}

export default FavorecidoService;
