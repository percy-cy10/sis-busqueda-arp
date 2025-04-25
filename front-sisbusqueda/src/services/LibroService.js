// Importar la instancia de Axios configurada
// Asegúrate de que "src/boot/axios" esté configurado correctamente

import { api } from "src/boot/axios";

class LibroService {
  static async getData(params) {
    try {
      return (await api.get("/api/libros", { params })).data;
    } catch (error) {
      console.error("Error al obtener los datos de libros:", error);
      throw error;
    }
  }

  static async get(id) {
    try {
      return (await api.get(`/api/libros/${id}`)).data;
    } catch (error) {
      console.error("Error al obtener el libro:", error);
      throw error;
    }
  }

  static async delete(id) {
    try {
      return await api.delete(`/api/libros/${id}`);
    } catch (error) {
      console.error("Error al eliminar el libro:", error);
      throw error;
    }
  }

  static async save(reg) {
    try {
      if (reg.id === undefined || reg.id === null) {
        return (await api.post("/api/libros", reg)).data;
      } else {
        return (await api.put(`/api/libros/${reg.id}`, reg)).data;
      }
    } catch (error) {
      console.error("Error al guardar el libro:", error);
      throw error;
    }
  }

  static async toggleEstado(id) {
    try {
      return (await api.patch(`/api/libros/${id}/toggle-estado`)).data;
    } catch (error) {
      console.error("Error al cambiar el estado del libro:", error);
      throw error;
    }
  }
}

export default LibroService;
