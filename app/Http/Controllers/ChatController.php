<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;

class ChatController extends Controller
{
    /**
     * Menampilkan antarmuka percakapan (index).
     */
    public function index()
    {
        // Ambil semua data percakapan dari database
        $conversations = Conversation::latest()->get();

        // Return view dengan data percakapan
        return view('admin.chatbotz.index', compact('conversations'));
    }

    public function user_index()
    {
        // Ambil semua data percakapan dari database
        $conversations = Conversation::latest()->get();

        // Return view dengan data percakapan
        return view('User.index', compact('conversations'));
    }

    /**
     * Menangani pengiriman pesan dan pembaruan respons chatbot.
     */
    public function sendAndUpdateResponse(Request $request)
    {
        // Validasi input dari permintaan
        $validated = $request->validate([
            'admin_message' => 'required|string',
            'stage' => 'required|string',
            'chatbot_response' => 'required|string',
        ]);

        // Simpan pesan admin ke dalam database
        $conversation = new Conversation();
        $conversation->user_input = $validated['admin_message'];
        $conversation->bot_response = $validated['chatbot_response'];
        $conversation->save();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.chatbot.index')->with('success', 'Pesan dan respon chatbot berhasil diperbarui.');
    }

    /**
     * Memperbarui data percakapan berdasarkan ID.
     */
    public function update(Request $request, $id)
    {
        // Validasi input dari permintaan
        $validated = $request->validate([
            'user_input' => 'required|string',
            'bot_response' => 'required|string',
        ]);

        // Cari data percakapan berdasarkan ID dan perbarui
        $conversation = Conversation::findOrFail($id);
        $conversation->update($validated);

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Percakapan berhasil diperbarui!');
    }

    /**
     * Menghapus data percakapan berdasarkan ID.
     */
    public function destroy($id)
    {
        // Cari data percakapan berdasarkan ID
        $conversation = Conversation::findOrFail($id);

        // Hapus data percakapan
        $conversation->delete();

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Percakapan berhasil dihapus!');
    }

    /**
     * Menyimpan pesan pengguna.
     */
    public function store(Request $request)
    {
        // Validasi input dari pengguna
        $validated = $request->validate([
            'user_message' => 'required|string',
        ]);

        // Simpan pesan pengguna ke database
        $conversation = new Conversation();
        $conversation->user_input = $validated['user_message'];
        $conversation->bot_response = "Terima kasih atas pesan Anda!"; // Respon default atau bisa diganti
        $conversation->save();

        // Redirect kembali dengan pesan sukses
        return redirect()->back()->with('success', 'Pesan Anda berhasil terkirim!');
    }
    public function chat(Request $request)
    {
        try {
            $request->validate([
                'user_input' => 'required|string',
            ]);

            $userInput = strtolower($request->input('user_input'));
            $response = \App\Models\Conversation::where('user_input', 'like', '%' . $userInput . '%')->first(); // Pastikan namespace model benar

            if ($response) {
                $responseMessage = $response->bot_response;
            } else {
                $responseMessage = 'Maaf, saya tidak mengerti pertanyaan Anda.';
            }

            return response()->json(['response' => $responseMessage]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Chatbot API error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan internal.'], 500);
        }
    }
}
