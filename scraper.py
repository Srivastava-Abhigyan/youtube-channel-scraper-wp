import yt_dlp
import json
import os
import sys
import traceback


def fetch_all_videos(channel_id):
    """Fetch all videos from a YouTube channel."""
    url = f"https://www.youtube.com/channel/{channel_id}/videos"
    ydl_opts = {
        'quiet': True,
        'extract_flat': False,
        'dump_single_json': True,
        'skip_download': True,
        'ignoreerrors': True,
    }

    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            print(f"Fetching videos from: {url}", file=sys.stderr)
            result = ydl.extract_info(url, download=False)
            
            if not result:
                print("No data returned from yt-dlp", file=sys.stderr)
                return [], "Unknown"
                
            if 'entries' in result:
                videos = [entry for entry in result['entries'] if entry and entry.get('id')]
                return videos, result.get('title', 'Unknown')
            else:
                return [result] if result.get('id') else [], result.get('title', 'Unknown')
                
    except Exception as e:
        print(f"Error fetching videos: {str(e)}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
        return [], "Unknown"

def main():
    """Main function to scrape YouTube channel videos."""
    if len(sys.argv) < 3:
        print("Usage: python scraper.py <channel_id> <output_file>", file=sys.stderr)
        sys.exit(1)

    channel_id = sys.argv[1]
    output_file = sys.argv[2]
    status_file = output_file.replace('.json', '_status.txt')
    
    print(f"Starting scrape for channel: {channel_id}", file=sys.stderr)
    print(f"Output file: {output_file}", file=sys.stderr)
    print(f"Status file: {status_file}", file=sys.stderr)

    try:
        # Create status file to indicate scraping in progress
        with open(status_file, 'w') as f:
            f.write("SCRAPING")
        
        videos, channel_name = fetch_all_videos(channel_id)
        print(f"Found {len(videos)} videos", file=sys.stderr)
        
        existing = []
        if os.path.exists(output_file):
            try:
                with open(output_file, 'r', encoding='utf-8') as f:
                    existing = json.load(f)
                    print(f"Loaded {len(existing)} existing videos", file=sys.stderr)
            except Exception as e:
                print(f"Error loading existing file: {str(e)}", file=sys.stderr)
                existing = []

        existing_urls = {item['url'] for item in existing}
        added = 0

        for entry in videos:
            video_id = entry.get('id')
            if not video_id:
                continue
                
            url = f"https://www.youtube.com/watch?v={video_id}"
            if url not in existing_urls:
                title = entry.get("title", "Untitled")
                thumbnail = f"https://i.ytimg.com/vi/{video_id}/hq720.jpg"
                
                existing.append({
                    "title": title,
                    "url": url,
                    "channel": channel_name,
                    "thumbnail": thumbnail
                })
                added += 1
                print(f"Added new video: {title}", file=sys.stderr)

        print(f"Total videos: {len(existing)}, Added: {added}", file=sys.stderr)
        
        # Ensure output directory exists
        os.makedirs(os.path.dirname(output_file), exist_ok=True)
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(existing, f, indent=4, ensure_ascii=False)
            
        print(f"Successfully saved to {output_file}", file=sys.stderr)
        
        # Update status file to indicate completion
        with open(status_file, 'w') as f:
            f.write("COMPLETED")
            
    except Exception as e:
        print(f"Error in main: {str(e)}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
        
        # Write error to status file
        with open(status_file, 'w') as f:
            f.write(f"ERROR: {str(e)}")
        
        sys.exit(1)

if __name__ == "__main__":
    # channel_id = 'UCFNjYL2tt8lqQwPRbFhfpGA'
    # result = fetch_all_videos(channel_id)
    # print(json.dumps(result, indent=2))
    main()

