@extends('layouts.app')

@section('content')
<div>
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
                <i class="fas fa-book"></i> Knowledge Base
            </h1>
            <p style="color: #6b7280; margin: 0;">Manage documents that inform the AI assistant's responses.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openUploadModal()">
            <i class="fas fa-upload"></i> Upload Document
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-4 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="color: #2563eb;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-value">{{ count($documents) }}</div>
            <div class="stat-label">Total Documents</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #10b981;">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-value">20 KB</div>
            <div class="stat-label">Total Size</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #f59e0b;">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="stat-value">Today</div>
            <div class="stat-label">Last Updated</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #8b5cf6;">
                <i class="fas fa-language"></i>
            </div>
            <div class="stat-value">1</div>
            <div class="stat-label">Languages</div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-folder-open"></i> Documents
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($documents as $document)
            <div class="document-item">
                <div class="d-flex align-center gap-2">
                    <div class="document-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h6 style="margin: 0; color: #1e3a8a;">{{ $document['title'] }}</h6>
                        <small style="color: #6b7280;">{{ $document['description'] }}</small>
                    </div>
                </div>
                <div class="d-flex align-center gap-2">
                    <span class="badge badge-info">{{ $document['size'] }}</span>
                    <small style="color: #6b7280;">Updated {{ $document['updated_at']->diffForHumans() }}</small>
                    <div class="action-buttons">
                        <button type="button" class="action-btn action-btn-view" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="action-btn action-btn-edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="action-btn action-btn-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div style="padding: 40px; text-align: center;">
                <i class="fas fa-folder-open" style="font-size: 3rem; color: #d1d5db;"></i>
                <p style="color: #6b7280; margin-top: 15px;">No documents uploaded yet.</p>
                <button type="button" class="btn btn-primary" onclick="openUploadModal()">
                    <i class="fas fa-upload"></i> Upload Your First Document
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Document Categories -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-tags"></i> Document Categories
        </div>
        <div class="card-body">
            <div class="grid grid-3">
                <div class="category-tag">
                    <i class="fas fa-gavel" style="color: #dc2626;"></i>
                    <span>Voting Rules</span>
                    <small>1 document</small>
                </div>
                <div class="category-tag">
                    <i class="fas fa-book-open" style="color: #2563eb;"></i>
                    <span>Documentation</span>
                    <small>1 document</small>
                </div>
                <div class="category-tag">
                    <i class="fas fa-question-circle" style="color: #10b981;"></i>
                    <span>FAQs</span>
                    <small>0 documents</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Supported Formats -->
    <div class="alert alert-info mt-4">
        <h6><i class="fas fa-info-circle"></i> Supported Document Formats</h6>
        <p style="margin-bottom: 0;">
            You can upload documents in the following formats:
            <strong>PDF</strong>, <strong>TXT</strong>, <strong>MD</strong> (Markdown), <strong>DOCX</strong>, <strong>HTML</strong>
        </p>
        <p style="margin-bottom: 0; margin-top: 10px;">
            Maximum file size: <strong>10 MB</strong>. Documents are processed and indexed for AI retrieval.
        </p>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="fas fa-upload"></i> Upload Document</h5>
            <button type="button" class="modal-close" onclick="closeUploadModal()">&times;</button>
        </div>
        <form>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Document Title</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter document title" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Brief description of the document"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select class="form-control" name="category">
                        <option value="rules">Voting Rules</option>
                        <option value="documentation">Documentation</option>
                        <option value="faq">FAQs</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">File</label>
                    <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #9ca3af;"></i>
                        <p style="margin: 10px 0 0; color: #6b7280;">Click to upload or drag and drop</p>
                        <small style="color: #9ca3af;">PDF, TXT, MD, DOCX, HTML (max 10MB)</small>
                        <input type="file" id="fileInput" name="file" style="display: none;" accept=".pdf,.txt,.md,.docx,.html">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload Document</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .document-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s;
    }
    .document-item:hover {
        background: #f9fafb;
    }
    .document-item:last-child {
        border-bottom: none;
    }
    .document-icon {
        width: 45px;
        height: 45px;
        background: #dbeafe;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
        font-size: 1.25rem;
    }

    .category-tag {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
    }
    .category-tag span {
        font-weight: 500;
        flex: 1;
    }
    .category-tag small {
        color: #6b7280;
    }

    .upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .upload-zone:hover {
        border-color: #2563eb;
        background: #f0f9ff;
    }

    /* Modal */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: white;
        border-radius: 8px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    }
    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h5 {
        margin: 0;
        color: #1e3a8a;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
    }
    .modal-body {
        padding: 20px;
    }
    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    function openUploadModal() {
        document.getElementById('uploadModal').style.display = 'flex';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    document.getElementById('uploadModal').addEventListener('click', function(e) {
        if (e.target === this) closeUploadModal();
    });
</script>
@endpush
