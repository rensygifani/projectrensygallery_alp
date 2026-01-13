<div class="modal fade" id="reviewModal{{ $item->id }}">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('review.store', $item->product) }}">
        @csrf

        <div class="modal-content pastel-form-card">
            <div class="modal-header">
                <h5 class="modal-title">Review {{ $item->product->name }}</h5>
            </div>

            <div class="modal-body">
                <select name="rating" class="form-select mb-2" required>
                    <option value="">Pilih Rating</option>
                    @for($i=5;$i>=1;$i--)
                        <option value="{{ $i }}">{{ $i }} ⭐</option>
                    @endfor
                </select>

                <textarea name="comment"
                    class="form-control"
                    placeholder="Tulis review..."></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn pastel-primary">Kirim</button>
            </div>
        </div>
    </form>
  </div>
</div>
